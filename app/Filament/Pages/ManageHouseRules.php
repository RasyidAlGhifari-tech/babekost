<?php

namespace App\Filament\Pages;

use App\Models\HouseRule;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageHouseRules extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Tata Tertib';

    protected static string $view = 'filament.pages.manage-house-rules';

    /**
     * Hari 7: tata tertib adalah SATU baris saja, berlaku untuk semua
     * kos (bagian 6 & 10) — jadi ini Page kustom, bukan Resource biasa
     * (tidak ada konsep "daftar" atau "tambah baru").
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'content' => HouseRule::current()->content,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Textarea::make('content')
                ->label('Isi tata tertib')
                ->required()
                ->rows(12),
        ])->statePath('data');
    }

    public function save(): void
    {
        $validated = $this->form->getState();

        HouseRule::current()->update([
            'content' => $validated['content'],
        ]);

        Notification::make()
            ->success()
            ->title('Tata tertib disimpan')
            ->send();
    }
}
