<?php

namespace App\Services;

use App\DTOs\CompanyData;
use App\Models\Company;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    public function __construct(
        protected FileNativeService $fileService
    ) {}

    public function createCompany(CompanyData $data, ?UploadedFile $logo = null): Company
    {
        return DB::transaction(function () use ($data, $logo) {
            $company = Company::create([
                'name' => $data->name,
                'ruc' => $data->ruc,
                'description' => $data->description,
                'address' => $data->address,
                'phone' => $data->phone,
                'email' => $data->email,
            ]);

            if ($logo) {
                $logoPath = $this->fileService->store($company, $logo);
                if ($logoPath) {
                    $company->logo = $logoPath;
                    $company->save();
                }
            }

            return $company;
        });
    }

    public function updateCompany(Company $company, CompanyData $data, ?UploadedFile $logo = null): Company
    {
        return DB::transaction(function () use ($company, $data, $logo) {

            $lockedCompany = Company::lockForUpdate()->find($company->id);

            if (! $lockedCompany) {
                throw new \Exception('La empresa que intentas editar ya no existe.');
            }

            $lockedCompany->update([
                'name' => $data->name,
                'ruc' => $data->ruc,
                'description' => $data->description,
                'address' => $data->address,
                'phone' => $data->phone,
                'email' => $data->email,
            ]);

            if ($logo) {
                $logoPath = $this->fileService->replace($lockedCompany, $logo, 'logo');
                if ($logoPath) {
                    $lockedCompany->logo = $logoPath;
                    $lockedCompany->save();
                }
            }

            return $lockedCompany;
        });
    }
}
