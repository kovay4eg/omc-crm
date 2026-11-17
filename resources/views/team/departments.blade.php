@foreach($departments as $department)
    @php
        $cleanName = trim(preg_replace(
            '/\s+/u',
            ' ',
            preg_replace('/^відділ\s+/iu', '', mb_strtolower($department->name)),
        ));
        $isAdministration = $department->isAdministration();
        $title = $isAdministration
            ? 'АДМІНІСТРАЦІЯ' : 'ВІДДІЛ '.mb_strtoupper($cleanName);
        $isInnovationProjectsDepartment = in_array($cleanName, [
            'інновацій та супроводу проєктів',
            'інновацій та супроводу проектів',
            'іновацій та супроводу проєктів',
            'іновацій та супроводу проектів',
        ], true);
        $isYouthDevelopmentNationalConsciousnessDepartment =
            $cleanName === 'розвитку молоді та національної свідомості';
        $normalizedCleanName = str_replace(["'", 'ʼ', '`'], '’', $cleanName);
        $isNonformalEducationCareerDepartment = in_array($normalizedCleanName, [
            'неформальної освіти та розвитку кар’єри',
            'неформальної освіти і розвитку кар’єри',
            'неформальної освіти та кар’єрного розвитку',
        ], true);
        $fixedDepartmentGallery = $isYouthDevelopmentNationalConsciousnessDepartment
            ? [
                'slug' => 'youth-development-national-consciousness',
                'directory' => 'youth-development-national-consciousness',
                'photos' => [
                    'youth-development-national-consciousness-01.webp',
                    'youth-development-national-consciousness-02.webp',
                    'youth-development-national-consciousness-03.webp',
                    'youth-development-national-consciousness-04.webp',
                    'youth-development-national-consciousness-05.webp',
                    'youth-development-national-consciousness-06.webp',
                    'youth-development-national-consciousness-07.webp',
                    'youth-development-national-consciousness-08.webp',
                    'youth-development-national-consciousness-09.webp',
                    'youth-development-national-consciousness-10.webp',
                    'youth-development-national-consciousness-11.webp',
                    'youth-development-national-consciousness-12.webp',
                    'youth-development-national-consciousness-13.webp',
                ],
            ]
            : ($isInnovationProjectsDepartment
                ? [
                    'slug' => 'innovation-projects',
                    'directory' => 'innovation-projects',
                    'photos' => [
                        'innovation-projects-01.webp', 'innovation-projects-02.webp',
                        'innovation-projects-03.webp', 'innovation-projects-04.webp',
                        'innovation-projects-05.webp', 'innovation-projects-06.webp',
                        'innovation-projects-07.webp', 'innovation-projects-08.webp',
                        'innovation-projects-09.webp', 'innovation-projects-10.webp',
                        'innovation-projects-11.webp', 'innovation-projects-12.webp',
                        'innovation-projects-13.webp', 'innovation-projects-14.webp',
                        'innovation-projects-15.webp', 'innovation-projects-16.webp',
                        'innovation-projects-17.webp', 'innovation-projects-18.webp',
                        'innovation-projects-19.webp', 'innovation-projects-20.webp',
                        'innovation-projects-21.webp', 'innovation-projects-22.webp',
                        'innovation-projects-23.webp', 'innovation-projects-24.webp',
                    ],
                ]
                : ($isNonformalEducationCareerDepartment
                    ? [
                        'slug' => 'nonformal-education-career',
                        'directory' => 'nonformal-education-career',
                        'photos' => [
                            'nonformal-education-career-01.webp', 'nonformal-education-career-02.webp',
                            'nonformal-education-career-03.webp', 'nonformal-education-career-04.webp',
                            'nonformal-education-career-05.webp', 'nonformal-education-career-06.webp',
                            'nonformal-education-career-07.webp', 'nonformal-education-career-08.webp',
                            'nonformal-education-career-09.webp', 'nonformal-education-career-10.webp',
                            'nonformal-education-career-11.webp', 'nonformal-education-career-12.webp',
                            'nonformal-education-career-13.webp', 'nonformal-education-career-14.webp',
                            'nonformal-education-career-15.webp', 'nonformal-education-career-16.webp',
                            'nonformal-education-career-17.webp', 'nonformal-education-career-18.webp',
                            'nonformal-education-career-19.webp', 'nonformal-education-career-20.webp',
                        ],
                    ]
                    : null));
        $sliderPhotos = $fixedDepartmentGallery !== null
            ? collect($fixedDepartmentGallery['photos'])
            : collect(array_values($department->slider_photos ?? []))
                ->filter(fn ($photo) => is_string($photo) && filled($photo));
        $showSlider = $fixedDepartmentGallery !== null
            ? $sliderPhotos->isNotEmpty()
            : $department->slider_enabled && $sliderPhotos->isNotEmpty();
    @endphp
    <button type="button" class="dept-row" data-department-toggle aria-expanded="false"
        aria-controls="department-{{ $department->id }}" id="department-toggle-{{ $department->id }}">
        <span class="dept-left">
            <span class="dept-icon"><img src="{{ asset('icons/flower.png') }}" alt=""></span>
            <span class="dept-title">{{ $title }}</span>
        </span>
        <span class="arrow"><span class="icon-arrow-custom" aria-hidden="true"></span></span>
    </button>
    <div class="department-panel" id="department-{{ $department->id }}" data-department-panel hidden
        role="region" aria-labelledby="department-toggle-{{ $department->id }}">
        @unless($isAdministration)
            <button type="button" class="department-toggle" data-department-section aria-expanded="false"
                aria-controls="department-description-{{ $department->id }}" id="department-description-toggle-{{ $department->id }}">
                <span>Чим займається відділ?</span><span class="icon-arrow-custom" aria-hidden="true"></span>
            </button>
            <div id="department-description-{{ $department->id }}" data-department-content hidden
                role="region" aria-labelledby="department-description-toggle-{{ $department->id }}">
                @if(filled($department->description))
                    <div class="department-description"><p>{{ $department->description }}</p></div>
                @endif
                @if($showSlider)
                    @if($fixedDepartmentGallery !== null)
                        <div class="innovation-gallery" data-innovation-projects-carousel data-department-photo-carousel
                            data-gallery="{{ $fixedDepartmentGallery['slug'] }}"
                            role="region" aria-roledescription="карусель"
                            aria-label="Фотографії роботи: {{ $department->name }}">
                            <div class="innovation-gallery__viewport" data-innovation-viewport tabindex="0">
                                <div class="innovation-gallery__track">
                                    @foreach($sliderPhotos as $photoIndex => $photo)
                                        @php
                                            $slideUrl = asset('images/team/departments/'.$fixedDepartmentGallery['directory'].'/'.$photo);
                                        @endphp
                                        <button type="button" class="innovation-gallery__slide" data-innovation-slide
                                            data-innovation-index="{{ $photoIndex }}"
                                            aria-label="Відкрити фото {{ $loop->iteration }} з {{ $sliderPhotos->count() }}">
                                            <img draggable="false" src="{{ $slideUrl }}"
                                                alt="{{ $department->name }}: фото {{ $loop->iteration }} з {{ $sliderPhotos->count() }}"
                                                loading="lazy" decoding="async" width="1200" height="900">
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            @if($sliderPhotos->count() > 1)
                                <button type="button" class="innovation-gallery__arrow innovation-gallery__arrow--previous"
                                    data-innovation-previous aria-label="Попередні фото">←</button>
                                <button type="button" class="innovation-gallery__arrow innovation-gallery__arrow--next"
                                    data-innovation-next aria-label="Наступні фото">→</button>
                                <div class="innovation-gallery__meta">
                                    <div class="innovation-gallery__dots" data-innovation-dots aria-label="Вибрати фото"></div>
                                    <p class="innovation-gallery__counter" data-innovation-counter aria-live="polite"></p>
                                </div>
                            @endif

                            <dialog class="innovation-lightbox" data-innovation-lightbox
                                aria-label="Збільшений перегляд фотографій відділу">
                                <button type="button" class="innovation-lightbox__close" data-innovation-lightbox-close
                                    aria-label="Закрити збільшений перегляд">×</button>
                                <button type="button" class="innovation-lightbox__arrow innovation-lightbox__arrow--previous"
                                    data-innovation-lightbox-previous aria-label="Попереднє фото">←</button>
                                <figure class="innovation-lightbox__figure">
                                    <img data-innovation-lightbox-image src="" alt="" width="1200" height="900">
                                    <figcaption class="innovation-lightbox__counter" data-innovation-lightbox-counter
                                        aria-live="polite"></figcaption>
                                </figure>
                                <button type="button" class="innovation-lightbox__arrow innovation-lightbox__arrow--next"
                                    data-innovation-lightbox-next aria-label="Наступне фото">→</button>
                            </dialog>
                        </div>
                    @else
                        <div class="department-carousel" data-team-carousel role="region" aria-roledescription="карусель"
                            aria-label="Фотографії роботи: {{ $department->name }}">
                            <div class="department-carousel-stage">
                                @foreach($sliderPhotos as $photoIndex => $photo)
                                    @php
                                        $slideUrl = route('departments.slide', [
                                            'department' => $department,
                                            'index' => $photoIndex,
                                            'v' => substr(sha1($photo), 0, 12),
                                        ]);
                                    @endphp
                                    <figure class="department-slide" data-slide aria-current="{{ $loop->first ? 'true' : 'false' }}"
                                        aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                                        <img draggable="false" src="{{ $slideUrl }}"
                                            alt="{{ $department->name }}: фото {{ $loop->iteration }} з {{ $sliderPhotos->count() }}"
                                            loading="lazy" decoding="async">
                                    </figure>
                                @endforeach
                            </div>
                            @if($sliderPhotos->count() > 1)
                                <div class="department-carousel-controls">
                                    <button type="button" class="carousel-arrow" data-carousel-previous aria-label="Попереднє фото">←</button>
                                    <div class="carousel-dots" aria-label="Вибрати фотографію">
                                        @foreach($sliderPhotos as $photoIndex => $photo)
                                            <button type="button" class="carousel-dot" data-slide-dot
                                                aria-label="Перейти до фото {{ $loop->iteration }}"
                                                aria-current="{{ $loop->first ? 'true' : 'false' }}"></button>
                                        @endforeach
                                    </div>
                                    <button type="button" class="carousel-arrow" data-carousel-next aria-label="Наступне фото">→</button>
                                    <button type="button" class="carousel-pause" data-carousel-pause aria-pressed="false"
                                        aria-label="Призупинити або відновити автоматичну зміну фото">Зупинити</button>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        @endunless

        @unless($isAdministration)
            <button type="button" class="department-toggle" data-department-section aria-expanded="false"
                aria-controls="department-employees-{{ $department->id }}" id="department-employees-toggle-{{ $department->id }}">
                <span>Склад відділу</span><span class="icon-arrow-custom" aria-hidden="true"></span>
            </button>
        @endunless
        <div id="department-employees-{{ $department->id }}"
            @unless($isAdministration)
                data-department-content hidden role="region" aria-labelledby="department-employees-toggle-{{ $department->id }}"
            @endunless>
            <div class="department-employees">
                @forelse($department->employees as $employee)
                    <article class="employee-card">
                        <div class="employee-photo {{ $employee->photo ? 'is-photo-loading' : 'is-photo-ready' }}" data-team-photo-frame>
                            @if($employee->photo)
                                <img src="{{ route('employees.photo', ['employee' => $employee, 'v' => substr(sha1($employee->photo), 0, 12)]) }}"
                                    alt="{{ $employee->last_name }} {{ $employee->first_name }}"
                                    loading="{{ $isAdministration ? 'eager' : 'lazy' }}"
                                    decoding="async"
                                    fetchpriority="{{ $isAdministration ? 'high' : 'low' }}"
                                    data-team-photo
                                    data-fallback-src="{{ asset('images/default-avatar.png') }}"
                                    @if($isAdministration) data-site-preloader-image @endif>
                            @else
                                <img src="{{ asset('images/default-avatar.png') }}" alt="" class="employee-placeholder" loading="lazy">
                            @endif
                        </div>
                        <div class="employee-copy">
                            <h3 class="employee-name">{{ $employee->last_name }} {{ $employee->first_name }}</h3>
                            <div class="employee-position">{{ $employee->position->name ?? '' }}</div>
                            @if(filled($employee->description))
                                <p class="employee-description">{{ $employee->description }}</p>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="text-muted py-3">Немає працівників</p>
                @endforelse
            </div>
        </div>
    </div>
@endforeach
