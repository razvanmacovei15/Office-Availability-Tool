<div style="padding: 1.5rem; background-color: white; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border: 1px solid #e5e7eb; min-height: 8rem; display: flex; flex-direction: column; justify-content: space-between; transition: box-shadow 0.2s ease-in-out;">
    <h2 style="font-size: 1.25rem; font-weight: bold; color: #1f2937; margin-bottom: 0.5rem;">{{ $building->name }}</h2>
    <h2 style="font-size: 0.75rem; ; color: #1f2937; margin-bottom: 0.5rem;">{{ $building->address }}</h2>
    <h2 style="font-size: 0.75rem; font-weight: bold; color: #1f2937; margin-bottom: 0.5rem;">{{ $building->city }}</h2>
{{--    <a href="{{ route('filament.admin.resources.buildings.edit', $building) }}"--}}
{{--       style="font-size: 0.875rem; color: #2563eb; text-decoration: none; font-weight: 500;">--}}
{{--        Edit Building--}}
{{--    </a>--}}
</div>
