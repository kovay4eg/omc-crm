@foreach($departments as $department)
    @php
        $cleanName = trim(preg_replace('/^відділ\s+/iu', '', mb_strtolower($department->name)));
        $isAdministration = $department->isAdministration();
        $title = $isAdministration
            ? 'АДМІНІСТРАЦІЯ' : 'ВІДДІЛ '.mb_strtoupper($cleanName);
        $sliderPhotos = collect(array_values($department->slider_photos ?? []))
            ->filter(fn ($photo) => is_string($photo) && filled($photo));
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
        @if(filled($department->photo))
            <figure class="department-photo">
                <img src="{{ route('departments.photo', ['department' => $department, 'v' => substr(sha1($department->photo), 0, 12)]) }}" alt="Фото працівників: {{ $department->name }}" loading="lazy">
            </figure>
        @endif

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
                @if($department->slider_enabled && $sliderPhotos->isNotEmpty())
                    <div class="department-carousel" data-team-carousel role="region" aria-roledescription="карусель"
                        aria-label="Фотографії роботи: {{ $department->name }}">
                        <div class="department-carousel-stage">
                            @foreach($sliderPhotos as $photoIndex => $photo)
                                <figure class="department-slide" data-slide aria-current="{{ $loop->first ? 'true' : 'false' }}"
                                    aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                                    <img draggable="false" src="{{ route('departments.slide', ['department' => $department, 'index' => $photoIndex, 'v' => substr(sha1($photo), 0, 12)]) }}"
                                        alt="{{ $department->name }}: фото {{ $loop->iteration }} з {{ $sliderPhotos->count() }}" loading="lazy">
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
            </div>
        @endunless

        <button type="button" class="department-toggle" data-department-section aria-expanded="false"
            aria-controls="department-employees-{{ $department->id }}" id="department-employees-toggle-{{ $department->id }}">
            <span>{{ $isAdministration ? 'Склад адміністрації' : 'Склад відділу' }}</span><span class="icon-arrow-custom" aria-hidden="true"></span>
        </button>
        <div id="department-employees-{{ $department->id }}" data-department-content hidden
            role="region" aria-labelledby="department-employees-toggle-{{ $department->id }}">
            <div class="department-employees">
                @forelse($department->employees as $employee)
                    <article class="employee-card">
                        <div class="employee-photo">
                            @if($employee->photo)
                                <img src="{{ route('employees.photo', ['employee' => $employee, 'v' => substr(sha1($employee->photo), 0, 12)]) }}"
                                    alt="{{ $employee->last_name }} {{ $employee->first_name }}" loading="lazy"
                                    onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.png') }}';">
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
