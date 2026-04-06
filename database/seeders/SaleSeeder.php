<?php

namespace Database\Seeders;

use App\Enums\AccountReceivableStatus;
use App\Enums\CashRegisterMovementType;
use App\Enums\CashRegisterSessionStatus;
use App\Models\AccountReceivable;
use App\Models\CashRegisterMovement;
use App\Models\CashRegisterSession;
use App\Models\Entity;
use App\Models\Inventory;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\User;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\LazyCollection;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configuración
        $totalSales = 500;

        // Modos: 'traditional', 'lazy', 'turbo'
        $mode = 'lazy';

        $this->command->info('Iniciando Seed de Ventas en modo: '.strtoupper($mode));

        // Obtener IDs
        $users = User::pluck('id');
        $clients = Entity::where('is_client', true)->pluck('id');
        $paymentMethods = PaymentMethod::whereIn('name', ['Efectivo', 'Tarjeta de crédito', 'Tarjeta de débito'])->pluck('id');
        $cashPaymentMethodId = PaymentMethod::where('name', 'Efectivo')->value('id');

        $variants = ProductVariant::with(['product.tax'])->get();
        // Map variant_id => inventory_id (take the first one found per variant to be simple)
        $variantInventoryMap = Inventory::pluck('id', 'product_variant_id');

        if ($users->isEmpty() || $clients->isEmpty() || $paymentMethods->isEmpty() || $variants->isEmpty()) {
            $this->command->error('Faltan datos previos (Usuarios, Clientes, Métodos de Pago, Variantes).');

            return;
        }

        if (! $cashPaymentMethodId) {
            $this->command->error('No se encontró el método de pago "Efectivo".');

            return;
        }

        $cashSalesCount = (int) ($totalSales * 0.80);
        $creditSalesCount = $totalSales - $cashSalesCount;

        // Crear sesiones de caja para distribuir las ventas (una sesión por día de actividad simulada)
        $sessions = $this->createCashRegisterSessions($users, $cashPaymentMethodId);

        match ($mode) {
            'traditional' => $this->runTraditional($cashSalesCount, $creditSalesCount, $variants, $users, $clients, $paymentMethods, $cashPaymentMethodId, $sessions),
            'lazy' => $this->runLazy($cashSalesCount, $creditSalesCount, 500, $users, $clients, $paymentMethods, $variants, $cashPaymentMethodId, $sessions),
            'turbo' => $this->runTurbo($cashSalesCount, $creditSalesCount, 1000, $users, $clients, $paymentMethods),
        };
    }

    /**
     * Nivel 1: Tradicional (Lento)
     * Crea registros uno por uno utilizando Factories y Eloquent simple.
     */
    private function runTraditional(int $cashCount, int $creditCount, $variants, $users, $clients, $paymentMethods, int $cashPaymentMethodId, $sessions)
    {
        $this->command->warn('⚠ Modo Tradicional: Esto puede tomar mucho tiempo...');

        $totalSales = $cashCount + $creditCount;
        $bar = $this->command->getOutput()->createProgressBar($totalSales);
        $bar->start();

        // Generar fechas secuenciales: distribuyendo las ventas en los últimos 365 días
        $startDate = now()->subDays(365);
        $salesIndex = 0;
        $intervalMinutes = ($totalSales > 1) ? (365 * 24 * 60) / ($totalSales - 1) : 0;

        // Inventories map for quick access (simple approach)
        // Note: For proper seeding, we should ideally fetch inventory fresh or track it.
        // But for standard seeding, we can just update database directly.

        // Tracking para balance de caja
        $sessionBalances = collect($sessions)->mapWithKeys(fn ($s) => [$s->id => $s->opening_balance]);

        // Helper para crear una venta
        $createSale = function ($isCredit) use ($variants, $users, $clients, $paymentMethods, $bar, $startDate, &$salesIndex, $intervalMinutes, $cashPaymentMethodId, $sessions, &$sessionBalances) {
            // Fecha secuencial: cada venta es más reciente que la anterior
            $date = $startDate->copy()->addMinutes((int) ($salesIndex * $intervalMinutes))->addMinutes(rand(0, 30));
            $salesIndex++;

            // 1. Determine Items
            $itemCount = rand(1, 4);
            $chosenVariants = $variants->random($itemCount);

            $detailsData = [];
            $saleSubTotal = Money::zero('NIO');
            $saleTaxAmount = Money::zero('NIO');

            // --- Prepared Data for Inventory Updates ---
            $inventoryUpdates = [];

            foreach ($chosenVariants as $variant) {
                $qty = rand(1, 3);

                // Use credit price if credit sale
                $price = $isCredit ? ($variant->credit_price ?? $variant->price) : $variant->price;

                if (! $price instanceof Money) {
                    $price = Money::ofMinor($price ?? 0, 'NIO');
                }

                $lineSubTotal = $price->multipliedBy($qty);

                $taxPercentage = $variant->product->tax->percentage ?? 0;
                $lineTax = $lineSubTotal->multipliedBy($taxPercentage / 100, RoundingMode::HALF_UP);

                $saleSubTotal = $saleSubTotal->plus($lineSubTotal);
                $saleTaxAmount = $saleTaxAmount->plus($lineTax);

                // Inventory Lookup
                $inventory = Inventory::where('product_variant_id', $variant->id)->first();
                $avgCost = $inventory ? $inventory->average_cost : Money::zero('NIO');
                $inventoryId = $inventory?->id;

                $detailsData[] = [
                    'product_variant_id' => $variant->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'sub_total' => $lineSubTotal,
                    'tax_amount' => $lineTax,
                    'tax_percentage' => $taxPercentage,
                    'currency' => 'NIO',
                    'discount_amount' => Money::zero('NIO'), // Assuming no discount for seed
                    'discount_percentage' => 0,
                    'discount' => false,
                    'unit_cost' => $avgCost, // Store historical cost
                ];

                // Track for Inventory Movement
                if ($inventory) {
                    $inventoryUpdates[] = [
                        'inventory' => $inventory,
                        'qty' => $qty,
                        'price' => $avgCost,
                        'total' => $avgCost->multipliedBy($qty),
                    ];
                }
            }

            $saleTotal = $saleSubTotal->plus($saleTaxAmount);

            // 2. Create Sale
            $sale = Sale::create([
                'is_credit' => $isCredit,
                'status' => 'completed',
                'currency' => 'NIO',
                'sub_total' => $saleSubTotal,
                'tax_amount' => $saleTaxAmount,
                'total' => $saleTotal,
                'user_id' => $users->random(),
                'client_id' => $clients->random(),
                'payment_method_id' => $paymentMethods->random(),

                'sale_date' => $date->toDateString(),
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            // 3. Save Details & Process Inventory
            foreach ($detailsData as $idx => $data) {
                // Ensure we remove extra keys not in model if using createMany?
                // Using create on relationship filters safe attributes mostly, but unit_cost needs to be there.
                // We stored unit_cost in data.
                $detail = $sale->saleDetails()->create($data);

                // --- INVENTORY MOVEMENT LOGIC ---
                // Find matching inventory update
                $updateData = $inventoryUpdates[$idx] ?? null; // Indexes match because we iterated chosenVariants

                if ($updateData) {
                    /** @var Inventory $inv */
                    $inv = $updateData['inventory'];
                    $qty = $updateData['qty'];
                    $cost = $updateData['price'];
                    $totalCost = $updateData['total'];

                    $stockBefore = (string) $inv->stock;
                    $inv->setAttribute('stock', bcsub((string) $inv->stock, (string) $qty, 4));
                    $inv->saveQuietly();

                    // Create Movement (using Model directly to bypass service complexity in seeder)
                    \App\Models\InventoryMovement::create([
                        'inventory_id' => $inv->id,
                        'type' => \App\Enums\InventoryMovementType::Sale,
                        'quantity' => $qty, // Positive quantity for the record
                        'stock_before' => $stockBefore,
                        'stock_after' => $inv->stock,
                        'unit_price' => $cost, // Cost price
                        'total_price' => $totalCost,
                        'currency' => 'NIO',
                        'notes' => "Venta #{$sale->id} (Seeder)",
                        'user_id' => $sale->user_id,
                        'sourceable_id' => $detail->id,
                        'sourceable_type' => \App\Models\SaleDetail::class,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                }
            }

            // 4. Create Account Receivable if Credit, or Cash Register Movement if Cash
            if ($isCredit) {
                AccountReceivable::create([
                    'sale_id' => $sale->id,
                    'client_id' => $sale->client_id,
                    'total_amount' => $saleTotal,
                    'balance' => $saleTotal,
                    'amount_paid' => Money::zero('NIO'),
                    'currency' => 'NIO',
                    'status' => AccountReceivableStatus::Pending,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            } else {
                // Venta al contado: registrar movimiento de caja
                $session = $this->findSessionForDate($sessions, $date);
                if ($session) {
                    $currentBalance = $sessionBalances[$session->id];
                    $newBalance = $currentBalance->plus($saleTotal);
                    $sessionBalances[$session->id] = $newBalance;

                    CashRegisterMovement::create([
                        'type' => CashRegisterMovementType::Sale,
                        'amount' => $saleTotal,
                        'balance_after' => $newBalance,
                        'currency' => 'NIO',
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'description' => "Venta #{$sale->id}",
                        'movement_at' => $date,
                        'session_id' => $session->id,
                        'user_id' => $sale->user_id,
                        'payment_method_id' => $cashPaymentMethodId,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                }
            }

            $bar->advance();
        };

        // Ventas al contado
        for ($i = 0; $i < $cashCount; $i++) {
            $createSale(false);
        }

        // Ventas a crédito
        for ($i = 0; $i < $creditCount; $i++) {
            $createSale(true);
        }

        $bar->finish();

        // Actualizar los balances de cierre de las sesiones
        $this->updateSessionClosingBalances($sessions, $sessionBalances);

        $this->command->info("\nTerminado.");
    }

    /**
     * Nivel 2: Lazy Collection + Factory (Equilibrado)
     * Utiliza memoria eficiente pero mantiene la magia de Eloquent/Factories.
     */
    private function runLazy(int $cashCount, int $creditCount, int $chunkSize, $users, $clients, $paymentMethods, $variants, int $cashPaymentMethodId, $sessions)
    {
        // Calcular fechas secuenciales para TODAS las ventas (contado + crédito)
        $totalSales = $cashCount + $creditCount;
        $startDate = now()->subDays(365);
        $intervalMinutes = ($totalSales > 1) ? (365 * 24 * 60) / ($totalSales - 1) : 0;
        $globalSalesIndex = 0;

        // Tracking para balance de caja
        $sessionBalances = collect($sessions)->mapWithKeys(fn ($s) => [$s->id => $s->opening_balance]);

        $this->command->info('Generando ventas al contado...');
        $this->createSalesLazy($cashCount, $chunkSize, false, $users, $clients, $paymentMethods, $variants, $startDate, $globalSalesIndex, $intervalMinutes, $cashPaymentMethodId, $sessions, $sessionBalances);

        $this->command->info('Generando ventas a crédito...');
        $this->createSalesLazy($creditCount, $chunkSize, true, $users, $clients, $paymentMethods, $variants, $startDate, $globalSalesIndex, $intervalMinutes, $cashPaymentMethodId, $sessions, $sessionBalances);

        // Actualizar los balances de cierre de las sesiones
        $this->updateSessionClosingBalances($sessions, $sessionBalances);
    }

    private function createSalesLazy(int $amount, int $chunkSize, bool $isCredit, $users, $clients, $paymentMethods, $variants, $startDate, int &$globalSalesIndex, float $intervalMinutes, int $cashPaymentMethodId, $sessions, &$sessionBalances)
    {
        if ($amount <= 0) {
            return;
        }

        $bar = $this->command->getOutput()->createProgressBar($amount);
        $bar->start();

        LazyCollection::times($amount)
            ->chunk($chunkSize)
            ->each(function ($chunk) use ($bar, $isCredit, $users, $clients, $paymentMethods, $variants, $startDate, &$globalSalesIndex, $intervalMinutes, $cashPaymentMethodId, $sessions, &$sessionBalances) {

                foreach ($chunk as $index) {
                    // Fecha secuencial: cada venta es más reciente que la anterior
                    $date = $startDate->copy()->addMinutes((int) ($globalSalesIndex * $intervalMinutes))->addMinutes(rand(0, 30));
                    $globalSalesIndex++;

                    // 1. Determine Items
                    $itemCount = rand(1, 4);
                    $chosenVariants = $variants->random($itemCount);

                    $detailsData = [];
                    $saleSubTotal = Money::zero('NIO');
                    $saleTaxAmount = Money::zero('NIO');

                    // --- Inventory Prepared Data ---
                    $inventoryUpdates = [];

                    foreach ($chosenVariants as $variant) {
                        $qty = rand(1, 4);

                        // Use credit price if credit sale
                        $price = $isCredit ? ($variant->credit_price ?? $variant->price) : $variant->price;

                        if (! $price instanceof Money) {
                            $price = Money::ofMinor($price ?? 0, 'NIO');
                        }

                        $lineSubTotal = $price->multipliedBy($qty);

                        $taxPercentage = $variant->product->tax->percentage ?? 0;
                        $lineTax = $lineSubTotal->multipliedBy($taxPercentage / 100, RoundingMode::HALF_UP);

                        $saleSubTotal = $saleSubTotal->plus($lineSubTotal);
                        $saleTaxAmount = $saleTaxAmount->plus($lineTax);

                        // Inventory Lookup (Needs to be efficient in loop, but accept overhead for correctness)
                        $inventory = Inventory::where('product_variant_id', $variant->id)->first();
                        $avgCost = $inventory ? $inventory->average_cost : Money::zero('NIO');

                        $detailsData[] = [
                            'product_variant_id' => $variant->id,
                            'quantity' => $qty,
                            'unit_price' => $price,
                            'sub_total' => $lineSubTotal,
                            'tax_amount' => $lineTax,
                            'tax_percentage' => $taxPercentage,
                            'currency' => 'NIO',
                            'discount_amount' => Money::zero('NIO'),
                            'discount_percentage' => 0,
                            'discount' => false,
                            'unit_cost' => $avgCost,
                        ];

                        if ($inventory) {
                            $inventoryUpdates[] = [
                                'inventory' => $inventory,
                                'qty' => $qty,
                                'cost' => $avgCost,
                                'total_cost' => $avgCost->multipliedBy($qty),
                            ];
                        }
                    }

                    $saleTotal = $saleSubTotal->plus($saleTaxAmount);

                    // 2. Create Sale
                    $sale = Sale::create([
                        'is_credit' => $isCredit,
                        'status' => 'completed',
                        'currency' => 'NIO',
                        'sub_total' => $saleSubTotal,
                        'tax_amount' => $saleTaxAmount,
                        'total' => $saleTotal,
                        'user_id' => $users->random(),
                        'client_id' => $clients->random(),
                        'payment_method_id' => $paymentMethods->random(),

                        'sale_date' => $date->toDateString(),
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);

                    // 3. Save Details & Inventory Movements
                    foreach ($detailsData as $idx => $data) {
                        $detail = $sale->saleDetails()->create($data);

                        // Inventory Logic
                        $updateData = $inventoryUpdates[$idx] ?? null;
                        if ($updateData) {
                            /** @var Inventory $inv */
                            $inv = $updateData['inventory'];
                            $qty = $updateData['qty'];
                            $cost = $updateData['cost'];
                            $totalCost = $updateData['total_cost'];

                            $stockBefore = (string) $inv->stock;
                            $inv->setAttribute('stock', bcsub((string) $inv->stock, (string) $qty, 4));
                            $inv->saveQuietly();

                            \App\Models\InventoryMovement::create([
                                'inventory_id' => $inv->id,
                                'type' => \App\Enums\InventoryMovementType::Sale,
                                'quantity' => $qty,
                                'stock_before' => $stockBefore,
                                'stock_after' => $inv->stock,
                                'unit_price' => $cost,
                                'total_price' => $totalCost,
                                'currency' => 'NIO',
                                'notes' => "Venta #{$sale->id} (Seeder)",
                                'user_id' => $sale->user_id,
                                'sourceable_id' => $detail->id,
                                'sourceable_type' => \App\Models\SaleDetail::class,
                                'created_at' => $date,
                                'updated_at' => $date,
                            ]);
                        }
                    }

                    // 4. Create Account Receivable if Credit, or Cash Register Movement if Cash
                    if ($isCredit) {
                        AccountReceivable::create([
                            'sale_id' => $sale->id,
                            'client_id' => $sale->client_id,
                            'total_amount' => $saleTotal,
                            'balance' => $saleTotal,
                            'amount_paid' => Money::zero('NIO'),
                            'currency' => 'NIO',
                            'status' => AccountReceivableStatus::Pending,
                            'created_at' => $date,
                            'updated_at' => $date,
                        ]);
                    } else {
                        // Venta al contado: registrar movimiento de caja
                        $session = $this->findSessionForDate($sessions, $date);
                        if ($session) {
                            $currentBalance = $sessionBalances[$session->id];
                            $newBalance = $currentBalance->plus($saleTotal);
                            $sessionBalances[$session->id] = $newBalance;

                            CashRegisterMovement::create([
                                'type' => CashRegisterMovementType::Sale,
                                'amount' => $saleTotal,
                                'balance_after' => $newBalance,
                                'currency' => 'NIO',
                                'reference_type' => Sale::class,
                                'reference_id' => $sale->id,
                                'description' => "Venta #{$sale->id}",
                                'movement_at' => $date,
                                'session_id' => $session->id,
                                'user_id' => $sale->user_id,
                                'payment_method_id' => $cashPaymentMethodId,
                                'created_at' => $date,
                                'updated_at' => $date,
                            ]);
                        }
                    }
                }

                $bar->advance($chunk->count());
            });

        $bar->finish();
        $this->command->info('');
    }

    /**
     * Nivel 3: Turbo (Inserción Directa)
     * Salta Eloquent. Construye arrays crudos e inserta en lotes. Velocidad máxima.
     */
    private function runTurbo(int $cashCount, int $creditCount, int $chunkSize, $users, $clients, $paymentMethods)
    {
        $this->command->info('🚀 Modo Turbo: Preparando motores...');
        $this->command->warn('⚠ ADVERTENCIA: En modo Turbo NO se generan detalles (items) de venta para máxima velocidad.');
        $this->command->warn("   Las ventas aparecerán con '0 Items' en el índice.");

        // Calcular fechas secuenciales para TODAS las ventas (contado + crédito)
        $totalSales = $cashCount + $creditCount;
        $startDate = now()->subDays(365);
        $intervalMinutes = ($totalSales > 1) ? (365 * 24 * 60) / ($totalSales - 1) : 0;
        $globalSalesIndex = 0;

        $this->createSalesTurbo($cashCount, $chunkSize, false, $users, $clients, $paymentMethods, $startDate, $globalSalesIndex, $intervalMinutes);
        $this->createSalesTurbo($creditCount, $chunkSize, true, $users, $clients, $paymentMethods, $startDate, $globalSalesIndex, $intervalMinutes);
    }

    private function createSalesTurbo(int $amount, int $chunkSize, bool $isCredit, $users, $clients, $paymentMethods, $startDate, int &$globalSalesIndex, float $intervalMinutes)
    {
        if ($amount <= 0) {
            return;
        }

        $bar = $this->command->getOutput()->createProgressBar($amount);
        $bar->start();

        LazyCollection::times($amount)
            ->chunk($chunkSize)
            ->each(function ($chunk) use ($bar, $isCredit, $users, $clients, $paymentMethods, $startDate, &$globalSalesIndex, $intervalMinutes) {

                $data = [];
                foreach ($chunk as $index) {
                    // Fecha secuencial: cada venta es más reciente que la anterior
                    $date = $startDate->copy()->addMinutes((int) ($globalSalesIndex * $intervalMinutes))->addMinutes(rand(0, 30));
                    $globalSalesIndex++;

                    $total = rand(5000, 200000); // Int cents
                    $taxAmount = rand(500, 20000); // Int cents
                    $subTotal = $total - $taxAmount;

                    $data[] = [
                        'sub_total' => $subTotal,
                        'total' => $total,
                        'status' => 'completed',
                        'currency' => 'NIO',
                        'is_credit' => $isCredit,
                        'tax_amount' => $taxAmount,
                        'user_id' => $users->random(),
                        'client_id' => $clients->random(),
                        'payment_method_id' => $paymentMethods->random(),

                        'quotation_id' => null,
                        'sale_date' => $date->format('Y-m-d'),
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                }

                Sale::insert($data); // Inserción masiva pura

                $bar->advance(count($data));
            });

        $bar->finish();
        $this->command->info('');
    }

    // =========================================================================
    // CASH REGISTER HELPER METHODS
    // =========================================================================

    /**
     * Crea sesiones de caja para cubrir el período de simulación.
     * Crea sesiones cerradas (para historial) distribuyendo los 365 días en sesiones semanales.
     * Reutiliza sesiones existentes si ya fueron creadas por otro seeder.
     *
     * @return \Illuminate\Support\Collection<CashRegisterSession>
     */
    private function createCashRegisterSessions($users, int $cashPaymentMethodId): \Illuminate\Support\Collection
    {
        // Intentar obtener sesiones existentes (creadas por PurchaseSeeder)
        $existingSessions = CashRegisterSession::orderBy('opened_at')->get();

        if ($existingSessions->isNotEmpty()) {
            $this->command->info("Reutilizando {$existingSessions->count()} sesiones de caja existentes.");

            return $existingSessions;
        }

        $this->command->info('Creando sesiones de caja...');

        $startDate = now()->subDays(365);
        $endDate = now();
        $sessions = collect();

        // Crear una sesión por semana (aproximadamente 52 sesiones)
        $currentDate = $startDate->copy();
        $openingBalance = Money::of(5000, 'NIO'); // Fondo de caja inicial

        while ($currentDate->lt($endDate)) {
            $sessionEndDate = $currentDate->copy()->addDays(7);
            if ($sessionEndDate->gt($endDate)) {
                $sessionEndDate = $endDate->copy();
            }

            $userId = $users->random();

            $session = CashRegisterSession::create([
                'opening_balance' => $openingBalance,
                'expected_closing_balance' => $openingBalance, // Se actualizará con los movimientos
                'actual_closing_balance' => $openingBalance,   // Se actualizará al cierre real
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
     * Actualiza el expected_closing_balance de las sesiones al final del seeding.
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
