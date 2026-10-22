@props(['client'])
@if ($client && ! $client->trashed())
    <a href="{{ route('clients.show', $client) }}" {{ $attributes->class(['font-medium text-primary-600']) }}>{{ $client->name }}</a>
@else
    <span {{ $attributes }}>{{ $client?->name ?? 'Client tidak tersedia' }} (diarsipkan)</span>
@endif
