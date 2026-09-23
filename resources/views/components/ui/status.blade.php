@props(['value'])
@php
    $map = [
        'paid' => ['Payée', 'bg-emerald-50 text-succes ring-succes/20'],
        'pending' => ['En attente', 'bg-amber-50 text-alerte ring-alerte/20'],
        'expired' => ['Expirée', 'bg-slate-100 text-slate-600 ring-slate-300'],
        'cancelled' => ['Annulée', 'bg-red-50 text-erreur ring-erreur/20'],
        'published' => ['Publié', 'bg-emerald-50 text-succes ring-succes/20'],
        'draft' => ['Brouillon', 'bg-slate-100 text-slate-600 ring-slate-300'],
        'completed' => ['Terminé', 'bg-indigo-50 text-indigo-700 ring-indigo-200'],
        'active' => ['Actif', 'bg-emerald-50 text-succes ring-succes/20'],
        'inactive' => ['Inactif', 'bg-slate-100 text-slate-600 ring-slate-300'],
        'valid' => ['Valide', 'bg-emerald-50 text-succes ring-succes/20'],
        'used' => ['Utilisé', 'bg-indigo-50 text-indigo-700 ring-indigo-200'],
        'successful' => ['Réussi', 'bg-emerald-50 text-succes ring-succes/20'],
        'failed' => ['Échoué', 'bg-red-50 text-erreur ring-erreur/20'],
        'already_used' => ['Déjà utilisé', 'bg-red-50 text-erreur ring-erreur/20'],
        'invalid' => ['Invalide', 'bg-red-50 text-erreur ring-erreur/20'],
        'admin' => ['Administrateur', 'bg-indigo-50 text-indigo-700 ring-indigo-200'],
        'agent' => ['Agent', 'bg-sky-50 text-sky-700 ring-sky-200'],
    ];
    [$label, $tone] = $map[$value] ?? [ucfirst((string) $value), 'bg-slate-100 text-slate-600 ring-slate-300'];
@endphp
<span {{ $attributes->class(['inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset', $tone]) }}>{{ $label }}</span>
