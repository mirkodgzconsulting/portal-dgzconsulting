<?php

namespace App\Filament\Cliente\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('filament-panels::auth/pages/edit-profile.form.name.label'))
            ->disabled()
            ->helperText('Para cambiar tu nombre, contacta a DGZ Consulting.');
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::auth/pages/edit-profile.form.email.label'))
            ->disabled()
            ->helperText('Para cambiar tu email de acceso, contacta a DGZ Consulting.');
    }

    protected function getSecondaryEmailFormComponent(): Component
    {
        return TextInput::make('secondary_email')
            ->label('Email secundario')
            ->email()
            ->maxLength(255);
    }

    protected function getPhoneFormComponent(): Component
    {
        return TextInput::make('phone')
            ->label('Teléfono / WhatsApp')
            ->tel()
            ->maxLength(30);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getSecondaryEmailFormComponent(),
                $this->getPhoneFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }
}
