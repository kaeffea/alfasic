<?php

declare(strict_types=1);

namespace Alfasic\Models;

class Employee extends Model
{
    /**
     * Tabela associada no MySQL
     */
    protected static string $table = 'employees';

    /**
     * Colunas onde a busca instantânea com LIKE deve pesquisar
     */
    protected static array $searchable = [
        'name',
        'document',
        'role_title',
        'branch',
        'department',
        'city',
        'phone',
        'driver_license'
    ];

    protected static array $fillable = [
        'name', 'document', 'rg', 'birth_date', 'birth_city', 'birth_state',
        'branch', 'department', 'role_title', 'work_shift', 'hire_date', 'contract_type',
        'base_salary', 'has_peril_bonus', 'benefits_cost', 'pix_key',
        'driver_license', 'driver_license_category', 'driver_license_expiry', 'has_mopp', 'certifications',
        'phone', 'email', 'emergency_contact_name', 'emergency_contact_phone',
        'address', 'address_number', 'neighborhood', 'city', 'state', 'zip_code',
        'notes', 'is_active',
    ];
}