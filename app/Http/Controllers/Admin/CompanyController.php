<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\CompanyData;
use App\Services\CompanyService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Models\Company;

class CompanyController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Company::class);
        $company = Company::first();
        if (!$company) {
            return redirect()->route('companies.create');
        }
        return redirect()->route('companies.show', $company);
    }

    public function create()
    {
        $this->authorize('create', Company::class);
        return view('admin.companies.create');
    }

    public function store(CompanyRequest $request, CompanyService $companyService)
    {
        $data = CompanyData::fromRequest($request->validated());
        $company = $companyService->createCompany($data, $request->file('logo'));
        return redirect()->route('companies.index')->with('success', 'Empresa creada correctamente.');
    }

    public function show(Company $company)
    {
        $this->authorize('view', $company);
        return view('admin.companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(CompanyRequest $request, Company $company, CompanyService $companyService)
    {
        $data = CompanyData::fromRequest($request->validated());
        $company = $companyService->updateCompany($company, $data, $request->file('logo'));
        return redirect()->route('companies.index')->with('updated', 'Compañía actualizada correctamente.');
    }
}
