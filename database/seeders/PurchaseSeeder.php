<?php

namespace Database\Seeders;

use App\Enums\AccountPayableStatus;
use App\Enums\CashRegisterMovementType;
use App\Enums\CashRegisterSessionStatus;
use App\Enums\InventoryMovementType;
use App\Models\AccountPayable;
use App\Models\CashRegisterMovement;
use App\Models\CashRegisterSession;
use App\Models\Entity;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\User;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\LazyCollection;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Settings
        $totalPurchases = 200; // Adjust as needed
        $chunkSize = 100;

        $this->command->info('Iniciando Seeder de Compras (Restocking)...');

        // Fetch IDs
        $suppliers = Entity::where('is_supplier', true)->pluck('id');
        $users = User::pluck('id');
        $paymentMethods = PaymentMethod::pluck('id');

        // Fetch Variants with Product/Tax for calculations
        // We load them all into memory since 50-100 variants is fine.
        // If thousands, we might need a different strategy, but for seeding this is usually OK.
        $variants = ProductVariant::with(['product.tax'])->get();

        if ($suppliers->isEmpty() || $users->isEmpty() || $paymentMethods->isEmpty() || $variants->isEmpty()) {
            $this->command->error('Faltan datos previos (Proveedores, Usuarios, Métodos de Pago, Variantes).');

            return;
        }

        $cashPaymentMethodId = PaymentMethod::where('name', 'Efectivo')->value('id');
        if (! $cashPaymentMethodId) {
            $this->command->error('No se encontró el método de pago "Efectivo".');

            return;
        }

        $cashCount = (int) ($totalPurchases * 0.70);
        $creditCount = $totalPurchases - $cashCount;

        // Generate Dates (Last 365 days)
        // We want purchases to be spread out.
        $startDate = now()->subDays(365);
        $intervalMinutes = ($totalPurchases > 1) ? (365 * 24 * 60) / ($totalPurchases - 1) : 0;
        $globalIndex = 0;

        // Crear o reutilizar sesiones de caja para distribuir las compras
        $sessions = $this->getOrCreateCashRegisterSessions($users);
        $sessionBalances = collect($sessions)->mapWithKeys(fn ($s) => [$s->id => $s->expected_closing_balance ?? $s->opening_balance]);

        $this->command->info("Generando $cashCount compras al contado y $creditCount al crédito...");

        $this->createPurchasesLazy($cashCount, $chunkSize, false, $users, $suppliers, $paymentMethods, $variants, $startDate, $globalIndex, $intervalMinutes, $cashPaymentMethodId, $sessions, $sessionBalances);
        $this->createPurchasesLazy($creditCount, $chunkSize, true, $users, $suppliers, $paymentMethods, $variants, $startDate, $globalIndex, $intervalMinutes, $cashPaymentMethodId, $sessions, $sessionBalances);

        // Actualizar balances de cierre de sesiones
        $this->updateSessionClosingBalances($sessions, $sessionBalances);

        $this->command->info('Seeding de Compras finalizado.');
    }

    private function createPurchasesLazy(
        int $amount,
        int $chunkSize,
        bool $isCredit,
        $users,
        $suppliers,
        $paymentMethods,
        $variants,
        $startDate,
        int &$globalIndex,
        float $intervalMinutes,
        int $cashPaymentMethodId,
        $sessions,
        &$sessionBalances
    ) {
        if ($amount <= 0) {
            return;
        }

        $bar = $this->command->getOutput()->createProgressBar($amount);
        $bar->start();

        LazyCollection::times($amount)
            ->chunk($chunkSize)
            ->each(function ($chunk) use ($bar, $isCredit, $users, $suppliers, $paymentMethods, $variants, $startDate, &$globalIndex, $intervalMinutes, $cashPaymentMethodId, $sessions, &$sessionBalances) {

                foreach ($chunk as $index) {
                    $date = $startDate->copy()->addMinutes((int) ($globalIndex * $intervalMinutes))->addMinutes(rand(0, 30));
                    $globalIndex++;

                    // 1. Determine Items
                    $itemCount = rand(1, 5);
                    $chosenVariants = $variants->random($itemCount);

                    $detailsData = [];
                    $purchaseSubTotal = Money::zero('NIO');
                    $purchaseTaxAmount = Money::zero('NIO');

                    $inventoryUpdates = [];

                    foreach ($chosenVariants as $variant) {
                        $qty = rand(10, 100); // Restocking quantities
                        $cost = $variant->cost; // Base cost

                        // Slightly vary cost to simulate market changes
                        if (rand(0, 1)) {
                            $cost = $cost->multipliedBy(rand(95, 105) / 100, RoundingMode::HALF_UP);
                        }

                        $lineSubTotal = $cost->multipliedBy($qty);

                        $taxPercentage = $variant->product->tax->percentage ?? 0;
                        $lineTax = $lineSubTotal->multipliedBy($taxPercentage / 100, RoundingMode::HALF_UP);

                        $purchaseSubTotal = $purchaseSubTotal->plus($lineSubTotal);
                        $purchaseTaxAmount = $purchaseTaxAmount->plus($lineTax);

                        // Prepare Inventory Update Data
                        $inventory = Inventory::firstOrCreate([
                            'product_variant_id' => $variant->id,
                        ], [
                            'stock' => 0,
                            'min_stock' => 10,
                            'average_cost' => $cost,
                            'currency' => 'NIO',
                        ]);

                        $detailsData[] = [
                            'product_variant_id' => $variant->id,
                            'quantity' => $qty,
                            'unit_price' => $cost, // In purchase detail, unit_price is usually the cost
                            'tax_percentage' => $taxPercentage,
                            'tax_amount' => $lineTax,
                            'currency' => 'NIO',
                        ];

                        $inventoryUpdates[] = [
                            'inventory' => $inventory,
                            'qty' => $qty,
                            'cost' => $cost,
                            'total_cost' => $lineSubTotal,
                        ];
                    }

                    $purchaseTotal = $purchaseSubTotal->plus($purchaseTaxAmount);

                    // 2. Create Purchase Header
                    $purchase = Purchase::create([
                        'reference' => 'COM-'.rand(10000, 99999), // Simple reference
                        'status' => 'received', // Auto-receive for seeder
                        'purchase_date' => $date,
                        'currency' => 'NIO',
                        'sub_total' => $purchaseSubTotal,
                        'tax_amount' => $purchaseTaxAmount,
                        'total' => $purchaseTotal,
                        'supplier_id' => $suppliers->random(),
                        'user_id' => $users->random(),
                        'payment_method_id' => $paymentMethods->random(),
                        'is_credit' => $isCredit,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);

                    // 3. Save Details & Update Inventory
                    foreach ($detailsData as $idx => $data) {
                        $detail = $purchase->details()->create($data);

                        // Inventory Logic
                        $updateData = $inventoryUpdates[$idx] ?? null;
                        if ($updateData) {
                            /** @var Inventory $inv */
                            $inv = $updateData['inventory'];
                            $qty = $updateData['qty'];
                            $cost = $updateData['cost'];
                            $totalCost = $updateData['total_cost'];

                            $stockBefore = (string) $inv->stock;

                            // Update weighted average cost?
                            // Formula: ((CurrentStock * AvgCost) + (NewQty * NewCost)) / (CurrentStock + NewQty)
                            // We can use the helper method if exists, or do it manually.
                            // Let's do it semi-manually for seed speed, or just simple average if stock is 0.

                            $currentStockVal = (string) ($inv->stock ?? 0);
                            $currentAvgCost = $inv->average_cost instanceof Money ? $inv->average_cost : Money::of(0, 'NIO');

                            // If stock is negative (shouldn't be), handle gracefully? No, assume positive.

                            $currentTotalVal = $currentAvgCost->multipliedBy($currentStockVal, RoundingMode::HALF_UP);
                            $newTotalVal = $currentTotalVal->plus($totalCost);
                            $newStockVal = bcadd($currentStockVal, (string) $qty, 4);

                            if ($newStockVal > 0) {
                                // Calculate new average cost
                                // Since Money doesn't support division by float easily without rounding context,
                                // we might need to be careful.
                                // Logic: TotalValue / TotalQty
                                $newAvgCost = $newTotalVal->dividedBy($newStockVal, RoundingMode::HALF_UP);
                                $inv->average_cost = $newAvgCost;
                            } else {
                                $inv->average_cost = $cost;
                            }

                            $inv->setAttribute('stock', $newStockVal);
                            $inv->saveQuietly();

                            // Create Movement
                            InventoryMovement::create([
                                'inventory_id' => $inv->id,
                                'type' => InventoryMovementType::Purchase,
                                'quantity' => $qty,
                                'stock_before' => $stockBefore,
                                'stock_after' => $inv->stock,
                                'unit_price' => $cost,
                                'total_price' => $totalCost,
                                'currency' => 'NIO',
                                'notes' => "Compra #{$purchase->reference} (Seeder)",
                                'user_id' => $purchase->user_id,
                                'sourceable_id' => $purchase->id, // Linking to Purchase Header usually, or Detail?
                                // Schema usually links to Header for Purchase/Sale types in many systems,
                                // but let's check SaleSeeder... it linked to Detail!
                                // "sourceable_id' => $detail->id, sourceable_type' => \App\Models\SaleDetail::class"
                                // OK, I will link to PurchaseDetail here too for consistency with SaleSeeder logic previously seen.
                                // Actually, checking ProductFactorySeeder (line 292): "'sourceable_id' => $purchase->id"
                                // Checking SaleSeeder (line 198): "'sourceable_id' => $detail->id"
                                // It seems inconsistent. Usually Movement relates to a specific Line Item.
                                // I will stick to PurchaseDetail as it is more granular and correct for tracking costs per item.
                                // But wait, ProductFactorySeeder viewed earlier linked to Purchase::class.
                                // Let's try to stick to PurchaseDetail if possible, but if the System expects Purchase, it might break.
                                // ProductSeeder (line 409): sourceable_id => $purchase->id.
                                // It seems the "Purchase" type links to the Header in existing seeders.
                                // I will use Purchase::class to be safe and consistent with ProductSeeder.
                                'sourceable_type' => Purchase::class,
                                'created_at' => $date,
                                'updated_at' => $date,
                            ]);
                        }
                    }

                    // 4. Create Account Payable if Credit, or Cash Register Movement if Cash
                    if ($isCredit) {
                        AccountPayable::create([
                            'purchase_id' => $purchase->id,
                            'supplier_id' => $purchase->supplier_id,
                            'total_amount' => $purchase->total,
                            'balance' => $purchase->total,
                            'amount_paid' => Money::zero($purchase->currency),
                            'currency' => $purchase->currency,
                            'status' => AccountPayableStatus::Pending,
                            'due_date' => $date->copy()->addDays(30),
                            'created_at' => $date,
                            'updated_at' => $date,
                        ]);
                    } else {
                        // Compra al contado: registrar salida de caja (Withdrawal)
                        $session = $this->findSessionForDate($sessions, $date);
                        if ($session) {
                            $currentBalance = $sessionBalances[$session->id];
                            $newBalance = $currentBalance->minus($purchaseTotal);
                            $sessionBalances[$session->id] = $newBalance;

                            CashRegisterMovement::create([
                                'type' => CashRegisterMovementType::Withdrawal,
                                'amount' => $purchaseTotal,
                                'balance_after' => $newBalance,
                                'currency' => 'NIO',
                                'reference_type' => Purchase::class,
                                'reference_id' => $purchase->id,
                                'description' => "Compra #{$purchase->reference}",
                                'movement_at' => $date,
                                'session_id' => $session->id,
                                'user_id' => $purchase->user_id,
                                'payment_method_id' => $cashPaymentMethodId,
                                'created_at' => $date,
                                'updated_at' => $date,
                            ]);
                        }
                    }

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->command->newLine();
    }

    // =========================================================================
    // CASH REGISTER HELPER METHODS
    // =========================================================================

    /**
     * Obtiene las sesiones de caja existentes o crea nuevas si no existen.
     * Reutiliza las sesiones creadas por SaleSeeder si ya existen.
     *
     * @return \Illuminate\Support\Collection<CashRegisterSession>
     */
    private function getOrCreateCashRegisterSessions($users): \Illuminate\Support\Collection
    {
        // Intentar obtener sesiones existentes (creadas por SaleSeeder)
        $existingSessions = CashRegisterSession::orderBy('opened_at')->get();

        if ($existingSessions->isNotEmpty()) {
            $this->command->info("Reutilizando {$existingSessions->count()} sesiones de caja existentes.");

            return $existingSessions;
        }

        // Si no existen, crear nuevas sesiones (similar a SaleSeeder)
        $this->command->info('Creando sesiones de caja...');

        $startDate = now()->subDays(365);
        $endDate = now();
        $sessions = collect();

        $currentDate = $startDate->copy();
        $openingBalance = Money::of(5000, 'NIO');

        while ($currentDate->lt($endDate)) {
            $sessionEndDate = $currentDate->copy()->addDays(7);
            if ($sessionEndDate->gt($endDate)) {
                $sessionEndDate = $endDate->copy();
            }

            $userId = $users->random();

            $session = CashRegisterSession::create([
                'opening_balance' => $openingBalance,
                'expected_closing_balance' => $openingBalance,
                'actual_closing_balance' => $openingBalance,
                'difference' => Money::zero('NIO'),
                'status' => CashRegisterSessionStatus::Closed,
                'currency' => 'NIO',
                'opened_at' => $currentDate->copy()->setTime(8, 0, 0),
                'closed_at' => $sessionEndDate->copy()->setTime(18, 0, 0),
                'notes' => 'Sesión generada por Seeder',
                'user_id' => $userId,
                'opened_by' => $userId,
                'closed_by' => $userId,
                'created_at' => $currentDate,
                'updated_at' => $sessionEndDate,
            ]);

            $sessions->push($session);
            $currentDate = $sessionEndDate->copy()->addDay();
        }

        $this->command->info("Creadas {$sessions->count()} sesiones de caja.");

        return $sessions;
    }

    /**
     * Encuentra la sesión de caja activa para una fecha dada.
     */
    private function findSessionForDate($sessions, $date): ?CashRegisterSession
    {
        return $sessions->first(function (CashRegisterSession $session) use ($date) {
            return $session->opened_at <= $date && $session->closed_at >= $date;
        });
    }

    /**
     * Actualiza el expected_closing_balance de las sesiones.
     */
    private function updateSessionClosingBalances($sessions, $sessionBalances): void
    {
        foreach ($sessions as $session) {
            $finalBalance = $sessionBalances[$session->id] ?? $session->opening_balance;
            $session->update([
                'expected_closing_balance' => $finalBalance,
                'actual_closing_balance' => $finalBalance,
            ]);
        }
    }
}
