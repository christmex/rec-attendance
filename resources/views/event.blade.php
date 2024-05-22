<x-filament::input.wrapper class="mb-2">
    <x-slot name="prefix">
        Kegiatan
    </x-slot>
    <x-filament::input.select wire:model="events">
        <option value="">--Pilih Kegiatan--</option>
        @foreach (\App\Models\Event::all() as $event)
            <option value="{{$event->id}}">{{$event->name}}</option>
        @endforeach
        <!-- <option value="reviewing">Reviewing</option>
        <option value="published">Published</option> -->
    </x-filament::input.select>
</x-filament::input.wrapper>