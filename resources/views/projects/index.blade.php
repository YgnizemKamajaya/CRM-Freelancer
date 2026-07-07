@php($money = fn ($value) => 'IDR '.number_format((float) $value, 0, ',', '.'))
<x-app-layout>
    <x-slot:title>Projects</x-slot:title>

    <div class="space-y-5">
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">Projects</h2>
                <p class="mt-1 text-sm text-gray-500">Delivery status, deadline, progress, and client context.</p>
            </div>
            <a href="{{ route('projects.create') }}" class="crm-button-primary"><x-icon name="plus" class="h-4 w-4" />Add Project</a>
        </div>

        <form method="GET" class="crm-card grid gap-3 p-4 md:grid-cols-[1fr_180px_220px_auto]">
            <input name="search" value="{{ request('search') }}" class="crm-field mt-0" placeholder="Search project">
            <select name="status" class="crm-field mt-0">
                <option value="">All statuses</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="client_id" class="crm-field mt-0">
                <option value="">All clients</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected((int) request('client_id') === $client->id)>{{ $client->name }}</option>
                @endforeach
            </select>
            <button class="crm-button-secondary">Filter</button>
        </form>

        @if ($projects->count())
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($projects as $project)
                    <article class="crm-card p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-gray-900"><a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a></h3>
                                <p class="mt-1 text-sm text-gray-500">{{ $project->client->name }}</p>
                            </div>
                            <x-status-badge :status="$project->status" />
                        </div>
                        <div class="mt-5">
                            <div class="flex justify-between text-xs font-medium text-gray-500">
                                <span>Progress</span>
                                <span>{{ $project->computed_progress }}%</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full bg-primary-600" style="width: {{ $project->computed_progress }}%"></div>
                            </div>
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                            <div><p class="text-gray-500">Budget</p><p class="font-medium text-gray-900">{{ $money($project->budget) }}</p></div>
                            <div><p class="text-gray-500">Deadline</p><p class="font-medium text-gray-900">{{ $project->deadline?->format('d M Y') ?? '-' }}</p></div>
                        </div>
                    </article>
                @endforeach
            </div>
            {{ $projects->links() }}
        @else
            <x-empty-state title="No projects found" text="Create a project manually or convert a won deal." action="Add Project" :href="route('projects.create')" />
        @endif
    </div>
</x-app-layout>
