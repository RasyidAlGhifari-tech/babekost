<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $room = $this->route('room');

        return [
            'building_id' => ['required', 'exists:buildings,id'],
            'number'      => ['required', 'string', 'max:20',
                Rule::unique('rooms')
                    ->where(fn ($q) => $q->where('building_id', $this->building_id)->whereNull('deleted_at'))
                    ->ignore($room?->id)],
            'type'            => ['nullable', 'string', 'max:50'],
            'capacity'        => ['required', 'integer', 'min:1', 'max:10'],
            'price_monthly'   => ['required', 'integer', 'min:0'],
            'price_quarterly' => ['required', 'integer', 'min:0'],
            'price_yearly'    => ['required', 'integer', 'min:0'],
            'special_status'  => ['required', Rule::in(['normal', 'perbaikan', 'nonaktif'])],
            'facilities'      => ['nullable', 'array'],
            'facilities.*'    => ['exists:facilities,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'building_id' => 'bangunan', 'number' => 'nomor kamar', 'capacity' => 'kapasitas',
            'price_monthly' => 'harga bulanan', 'price_quarterly' => 'harga kuartalan',
            'price_yearly' => 'harga tahunan', 'special_status' => 'status',
        ];
    }

    public function messages(): array
    {
        return ['number.unique' => 'Nomor kamar ini sudah ada di bangunan tersebut.'];
    }
}