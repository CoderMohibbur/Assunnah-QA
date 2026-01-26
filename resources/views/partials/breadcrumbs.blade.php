@props([
  'items' => [] // [['label'=>'হোম','url'=>'/'], ...]
])

<nav class="text-sm">
  <ol class="flex flex-wrap items-center gap-2 text-slate-600">
    @foreach($items as $i)
      @if(!$loop->last)
        <li>
          <a href="{{ $i['url'] }}" class="hover:underline font-semibold text-slate-700">
            {{ $i['label'] }}
          </a>
        </li>
        <li class="opacity-60">/</li>
      @else
        <li class="font-extrabold text-slate-900">{{ $i['label'] }}</li>
      @endif
    @endforeach
  </ol>
</nav>
