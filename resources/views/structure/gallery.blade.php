<div
    class="structure-carousel mb-5"
    data-structure-carousel
    data-gallery-key="{{ $galleryKey }}"
    role="region"
    aria-roledescription="карусель"
    aria-label="{{ $galleryLabel }}"
>
    <div class="structure-carousel__viewport" data-structure-viewport tabindex="0">
        <div class="structure-carousel__track">
            @foreach ($photos as $photo)
                <figure
                    class="structure-carousel__slide"
                    data-structure-slide
                    aria-label="Фото {{ $loop->iteration }} з {{ $loop->count }}"
                >
                    <img
                        src="{{ asset('images/structure/' . $galleryKey . '/' . $photo['file']) }}"
                        width="1600"
                        height="1200"
                        loading="lazy"
                        decoding="async"
                        alt="{{ $photo['alt'] }}"
                    >
                </figure>
            @endforeach
        </div>
    </div>

    <button
        type="button"
        class="structure-carousel__arrow structure-carousel__arrow--previous"
        data-structure-previous
        aria-label="Попередні фото"
    >←</button>
    <button
        type="button"
        class="structure-carousel__arrow structure-carousel__arrow--next"
        data-structure-next
        aria-label="Наступні фото"
    >→</button>

    <div class="structure-carousel__meta">
        <div class="structure-carousel__dots" data-structure-dots aria-label="Вибрати фото"></div>
        <p class="structure-carousel__counter mb-0" data-structure-counter aria-live="polite"></p>
    </div>
</div>
