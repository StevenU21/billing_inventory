<?php

namespace App\Services;

use App\DTOs\AuditIndexRowData;
use App\Models\AccountReceivable;
use App\Models\Audit;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Deifhelt\ActivityPresenter\Facades\ActivityPresenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BaseQueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

final class AuditIndexService
{
    /**
     * @return array{0: LengthAwarePaginator, 1: \Illuminate\Database\Eloquent\Collection<int, User>, 2: Collection<int, array{value: string, label: string}>}
     */
    public function build(Builder|BaseQueryBuilder $groupedQuery, int $perPage = 10): array
    {
        $excludedModels = [
            SaleDetail::class,
            \App\Models\ProductAttributeValue::class,
            \App\Models\ProductPriceHistory::class,
            \App\Models\PurchaseDetail::class,
            \App\Models\QuotationDetail::class,
            \App\Models\AccountReceivablePayment::class,
            \App\Models\InventoryMovement::class,
        ];

        $groupedQuery->whereNotIn('subject_type', $excludedModels);

        $activities = ActivityPresenter::presentGrouped(
            query: $groupedQuery,
            perPage: $perPage,
            latestIdColumn: 'id',
            loadRelations: function ($activityQuery) {
                $activityQuery->with(['causer', 'subject']);
            },
            afterFetch: function ($activities) {
                $activities->loadMorph('subject', [
                    Inventory::class => ['productVariant.product'],
                    Sale::class => ['client'],
                    AccountReceivable::class => ['entity'],
                    SaleDetail::class => ['productVariant.product'],
                    \App\Models\ProductVariant::class => ['product', 'attributeValues.attribute'],
                ]);
            },
            mapGroupRow: function ($groupRow, $activity, $presentation) {
                $encodedSubjectType = ActivityPresenter::encodeSubjectType($groupRow->subject_type);

                $showUrl = null;

                if (isset($groupRow->subject_type, $groupRow->subject_id)) {
                    $showUrl = route('audits.show', [
                        'subjectType' => $encodedSubjectType,
                        'subjectId' => $groupRow->subject_id,
                    ]);
                }

                $date = $activity->updated_at ?? $activity->created_at;
                $formattedDate = $date ? Carbon::parse($date)->format('d/m/Y H:i:s') : '—';

                $base = class_basename($groupRow->subject_type);
                $key = 'activity-presenter::logs.models.'.$base;
                $subjectTypeLabel = Lang::has($key) ? __($key) : $base;

                return new AuditIndexRowData(
                    id: (int) ($groupRow->id ?? 0),
                    userName: $presentation->getCauserLabel(),
                    event: $presentation->getEventLabel(),
                    subjectType: $subjectTypeLabel,
                    subjectName: $presentation->getSubjectLabel(),
                    date: $formattedDate,
                    count: (int) ($groupRow->count ?? 1),
                    showUrl: $showUrl,
                );
            }
        );

        $causerIds = Audit::query()->whereNotNull('causer_id')->distinct()->pluck('causer_id');
        $allCausers = User::whereIn('id', $causerIds)->get();

        $allModels = Audit::select('subject_type')
            ->whereNotIn('subject_type', $excludedModels)
            ->distinct()
            ->pluck('subject_type');

        $modelOptions = $allModels
            ->filter()
            ->map(function ($modelType) {
                $base = class_basename($modelType);
                $key = 'activity-presenter::logs.models.'.$base;
                $label = Lang::has($key) ? __($key) : $base;

                return [
                    'value' => $modelType,
                    'label' => $label,
                ];
            })
            ->values();

        return [$activities, $allCausers, $modelOptions];
    }
}
