<p>Товар <strong>"{{ $product->title }}"</strong> знову в наявності!</p>

<a href="{{ frontend_url('/'. $product->sluggable?->slug) }}">
    Переглянути товар
</a>
