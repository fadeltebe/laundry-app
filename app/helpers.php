<?php

use Illuminate\Support\Facades\Auth;

function is_admin(): bool
{
    return Auth::check() && Auth::user()->hasRole('Admin');
}

function is_owner(): bool
{
    return Auth::check() && Auth::user()->hasRole('Owner');
}

function is_superadmin(): bool
{
    return Auth::check() && Auth::user()->hasRole('super_admin');
}
