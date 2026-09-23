@props(['id', 'items'])

<script type="application/json" id="{{ $id }}">@json($items->map->toPlayerTrack()->values())</script>
