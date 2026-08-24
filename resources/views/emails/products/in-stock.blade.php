<x-mail::message>
# Товар знову в наявності!

Товар **«{{ $product->title }}»** знову в наявності!

<x-mail::button :url="frontend_url('/' . $product->sluggable?->slug)">
Переглянути товар
</x-mail::button>

Дякуємо,<br>
{{ config('app.name') }}
</x-mail::message>
