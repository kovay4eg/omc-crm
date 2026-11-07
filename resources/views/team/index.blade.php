<link rel="stylesheet" href="{{ asset('css/team-departments.css') }}?v=20261107">
<section id="team-section" class="team-section">
<div class="team-hero">

    <div class="container">

        <div class="team-title-block">

            <div class="team-hero-bg" id="heroBg">
                КОМАНДА КОМАНДА КОМАНДА КОМАНДА КОМАНДА
            </div>

            <h1 class="team-title">
                КОМАНДА
            </h1>

            <div class="team-desc">
                Команда ПОМЦ – люди, які перетворюють твої сміливі ідеї на реальні можливості 💙
            </div>

        </div>

    </div>

</div>

<div class="container">

    <!-- TEAM PHOTO -->

    <div class="team-banner">

       @if(isset($settings) && !empty($settings->team_banner))

            <img
                src="{{ \App\Support\MediaUrl::storage($settings->team_banner) }}"
                class="team-banner-image"
                alt="Team Photo"
            >

        @else

            <div class="team-banner-placeholder">
                Team Photo
            </div>

        @endif

    </div>

    @include('team.departments')
</div>
<script src="{{ asset('js/team-departments.js') }}?v=20261103" defer></script>
</section>
