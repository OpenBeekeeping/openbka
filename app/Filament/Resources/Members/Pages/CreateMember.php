<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use App\Support\Access;
use Filament\Resources\Pages\CreateRecord;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Branch admins can only keep members in their own branch.
        if ($branchId = Access::branchId()) {
            $data['branch_id'] = $branchId;
        }

        return $data;
    }
}
