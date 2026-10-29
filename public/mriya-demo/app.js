(() => {
  const savedProfile = (() => {
    try { return JSON.parse(localStorage.getItem('mriia_demo_profile') || '{}'); }
    catch { return {}; }
  })();
  const savedMiraMemory = (() => {
    try { return JSON.parse(localStorage.getItem('mriia_mira_memory') || '{}'); }
    catch { return {}; }
  })();

  const state = {
    sessionId: sessionStorage.getItem('mriia_session') ||
      (crypto.randomUUID?.() ?? `demo-${Date.now()}-${Math.random().toString(16).slice(2)}`),
    currentScreen: 'home',
    role: savedProfile.role || 'youth',
    userName: savedProfile.name || '',
    welcomeRole: savedProfile.role || 'youth',
    employment: savedProfile.employment || '',
    position: savedProfile.position || '',
    affiliation: savedProfile.affiliation || '',
    profileVisible: savedProfile.profileVisible !== false,
    currentStory: 'center',
    miraMemory: {
      name: savedMiraMemory.name || savedProfile.name || '',
      role: savedMiraMemory.role || savedProfile.role || 'youth',
      tone: savedMiraMemory.tone || 'тепло й коротко',
      interests: Array.isArray(savedMiraMemory.interests) ? savedMiraMemory.interests.slice(0, 6) : [],
      actions: Number(savedMiraMemory.actions || 0)
    },
    selectedEvent: null,
    savedEvents: [],
    tour: 0,
    tourActive: false,
    tourTarget: null,
    tourAdvancing: false,
    tourAwaitingContinue: false,
    tourPraiseText: '',
    tourBranch: '',
    tourRenderId: 0,
    pendingTeamInterestButton: null,
    miraSound: true,
    voiceListening: false,
    feedMode: 'for-you',
    feedExtrasLoaded: false,
    candidateFilter: 'recommended',
    lastVacancy: {
      title: 'Асистент/ка комунікацій',
      category: 'Маркетинг і комунікації',
      city: 'Полтава',
      format: 'Гібридний',
      skills: ['SMM', 'тексти', 'Canva', 'комунікація']
    },
    startedAt: Date.now()
  };
  sessionStorage.setItem('mriia_session', state.sessionId);

  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
  const API_BASE = 'api';
  const CAN_USE_API = location.protocol === 'http:' || location.protocol === 'https:';
  let modalReturnFocus = null;

  const stories = {
    center: { author: 'Полтавський ОМЦ', avatar: 'ОМЦ', avatarClass: 'center-story', time: '15 хв', icon: '⚡', title: 'Сьогодні створюємо проєкти', text: 'Є ще 12 місць. Натисни нижче, щоб побачити подію.', cta: 'Переглянути подію', destination: 'event', profile: 'center-profile' },
    olya: { author: 'Оля Марченко', avatar: 'ОМ', avatarClass: 'person-story', time: '34 хв', icon: '📚', title: 'Книгообмін уже стартував', text: 'Зібрали 30 книжок і шукаємо людину для афіші та короткого відео.', cta: 'Долучитися до команди', destination: 'community', profileType: 'person', details: 'Дизайн · волонтерство · Полтава' },
    vector: { author: 'Агенція «Вектор»', avatar: 'V', avatarClass: 'employer-story', time: '1 год', icon: '🚀', title: 'Оплачуване стажування', text: 'Гібридний формат, наставник і прозорий діапазон оплати.', cta: 'Переглянути вакансію', destination: 'career', profile: 'employer-profile' },
    volunteers: { author: 'Спільнота волонтерів', avatar: '∞', avatarClass: 'community-story', time: '2 год', icon: '🤝', title: 'Потрібні 8 волонтерів', text: 'Допоможи провести фестиваль громади й отримай підтверджені години.', cta: 'Знайти волонтерство', destination: 'community', profileType: 'organization', details: 'Події · допомога громадам · 1 240 учасників' },
    greenhub: { author: 'GreenHub', avatar: 'GH', avatarClass: 'wellbeing-story', time: '3 год', icon: '🌱', title: 'Чисте місто за одну суботу', text: 'Прибирання, сортування і невеликий пікнік після спільної дії.', cta: 'Переглянути ініціативу', destination: 'explore', profileType: 'organization', details: 'Екологія · волонтерство · Полтавщина' },
    student: { author: 'Студентська рада', avatar: 'СР', avatarClass: 'person-story', time: '5 год', icon: '🎓', title: 'Шукаємо представників факультетів', text: 'Відкрита зустріч для тих, хто хоче впливати на студентське життя.', cta: 'Познайомитися', destination: 'community', profileType: 'organization', details: 'Освіта · студентські ініціативи · Полтава' }
  };

  function finishPreloader() {
    const preloader = $('#brandPreloader');
    if (!preloader || preloader.hidden) return;
    preloader.classList.add('is-leaving');
    setTimeout(() => { preloader.hidden = true; }, 650);
  }
  const preloaderDelay = matchMedia('(prefers-reduced-motion: reduce)').matches ? 450 : 2700;
  if (document.readyState === 'complete') setTimeout(finishPreloader, preloaderDelay);
  else window.addEventListener('load', () => setTimeout(finishPreloader, preloaderDelay), { once: true });

  const candidateSkillGroups = [
    { category: 'SMM', skills: ['SMM','копірайтинг','Canva','Reels','аналітика'] },
    { category: 'Дизайн', skills: ['UI/UX','Figma','Canva','Photoshop','брендинг'] },
    { category: 'IT', skills: ['HTML/CSS','JavaScript','React','no-code','QA'] },
    { category: 'Проєкти', skills: ['проєктний менеджмент','фасилітація','Excel','грантові заявки','комунікація'] },
    { category: 'Відео', skills: ['відеомонтаж','CapCut','DaVinci Resolve','сценарії','мобільна зйомка'] },
    { category: 'Освіта', skills: ['тренінги','презентації','менторство','англійська','фасилітація'] },
    { category: 'Соціальна сфера', skills: ['психологія','консультування','інклюзивність','медіація','комунікація'] },
    { category: 'HR', skills: ['рекрутинг','інтерв’ю','онбординг','Excel','комунікація'] }
  ];
  const candidateFirstNames = ['Аліна','Богдан','Вікторія','Данило','Емілія','Захар','Ірина','Кирило','Леся','Максим','Надія','Олексій','Поліна','Роман','Софія','Тарас','Уляна','Федір','Христина','Ярослав'];
  const candidateCities = ['Полтава','Кременчук','Миргород','Лубни','Київ','Харків','Дніпро','Львів','Одеса','онлайн'];
  const candidatePool = Array.from({ length: 100 }, (_, index) => {
    const group = candidateSkillGroups[index % candidateSkillGroups.length];
    const rotated = group.skills.map((_, skillIndex) => group.skills[(skillIndex + index) % group.skills.length]);
    const secondary = candidateSkillGroups[(index + 3) % candidateSkillGroups.length].skills[index % 5];
    return {
      id: index + 1,
      name: `${candidateFirstNames[index % candidateFirstNames.length]} ${String.fromCharCode(65 + (index * 7) % 26)}.`,
      city: candidateCities[(index * 3) % candidateCities.length],
      category: group.category,
      skills: [...rotated.slice(0, 4), secondary],
      experience: index % 4 === 0 ? 'без комерційного досвіду' : index % 4 === 1 ? '1 підтверджений проєкт' : index % 4 === 2 ? `${20 + index} волонтерських годин` : 'відкритий профіль до стажування'
    };
  });

  function vacancyKeywords() {
    return [state.lastVacancy.title, state.lastVacancy.category, ...(state.lastVacancy.skills || [])]
      .join(' ').toLowerCase().split(/[^a-zа-яіїєґ0-9+/#]+/i).filter(word => word.length > 2);
  }

  function candidateScore(candidate) {
    const keywords = vacancyKeywords();
    const haystack = `${candidate.category} ${candidate.skills.join(' ')}`.toLowerCase();
    const overlaps = [...new Set(keywords.filter(word => haystack.includes(word)))].length;
    const cityBonus = candidate.city === state.lastVacancy.city || candidate.city === 'онлайн' || state.lastVacancy.format === 'Онлайн' ? 6 : 0;
    return Math.min(98, 52 + overlaps * 9 + cityBonus + (candidate.id % 7));
  }

  function candidateMarkup(candidate) {
    const score = candidateScore(candidate);
    const initials = candidate.name.replace('.', '').split(' ').map(part => part[0]).join('').slice(0, 2);
    return `<article data-candidate-id="${candidate.id}" data-category="${candidate.category}"><span class="avatar a${candidate.id % 3 + 1}">${initials}</span><div><h4>${candidate.name} <small>Demo ${String(candidate.id).padStart(3, '0')}</small></h4><p class="candidate-skills">${candidate.skills.join(' · ')} · ${candidate.city}</p><em>${candidate.experience}</em><p class="candidate-demo-note">Повністю вигаданий профіль для демонстрації</p></div><span class="candidate-match">${score}%</span><button data-action="invite-talent">Запросити</button></article>`;
  }

  function renderCandidates(query = $('#talentSearch')?.value || '', filter = state.candidateFilter) {
    const normalized = query.trim().toLowerCase();
    let candidates = candidatePool.map(candidate => ({ ...candidate, score: candidateScore(candidate) }));
    if (normalized) candidates = candidates.filter(candidate => `${candidate.name} ${candidate.city} ${candidate.category} ${candidate.skills.join(' ')}`.toLowerCase().includes(normalized));
    if (!normalized && filter === 'recommended') candidates = candidates.filter(candidate => candidate.score >= 68);
    else if (filter !== 'all' && filter !== 'recommended') candidates = candidates.filter(candidate => candidate.category === filter || candidate.skills.some(skill => skill.toLowerCase().includes(filter.toLowerCase())));
    candidates.sort((a, b) => b.score - a.score || a.id - b.id);
    $('#employerTalentList').innerHTML = candidates.length ? candidates.map(candidateMarkup).join('') : '<article class="empty-state"><p>Нічого не знайдено. Спробуйте іншу навичку або місто.</p></article>';
    $('#candidateCount').textContent = candidates.length;
    $('#candidateMatchContext').textContent = `Відповідність вакансії «${state.lastVacancy.title}»`;
    const recommendations = candidatePool.map(candidate => ({ ...candidate, score: candidateScore(candidate) })).sort((a,b) => b.score - a.score).slice(0, 3);
    $('#employerRecommendations').innerHTML = recommendations.map(candidateMarkup).join('');
    $('#recommendationContext').textContent = `За демовакансією «${state.lastVacancy.title}» · ${state.lastVacancy.city} · ${state.lastVacancy.format}`;
  }

  const moderationRules = [
    { pattern: /(хуй|хує|хуя|пизд|бляд|їбат|йоб|у[єе]б|мудак|довбо[йїо]|шлюх)/iu, message: 'Непристойна або образлива лексика' },
    { pattern: /(ідіот|дебіл|тупиц|лох(?!ація)|нікчем)/iu, message: 'Образливе формулювання' },
    { pattern: /(нігер|хохол|кацап|жид(?!к)|чурк)/iu, message: 'Мова ненависті або принизливе позначення' }
  ];
  const vacancyFairnessRules = [
    { pattern: /тільки\s+(чоловік|чоловіки|жінк|дівчат|хлопц)/iu, message: 'Дискримінаційна вимога за статтю' },
    { pattern: /до\s*\d{2}\s*рок(ів|и)?/iu, message: 'Вікове обмеження без обґрунтованої вимоги' },
    { pattern: /(без\s+дітей|неодружен|незаміжн|слов['’]?янськ)/iu, message: 'Дискримінаційна вимога до кандидата' }
  ];

  function moderateField(field) {
    if (!field.matches('input[type="text"],input[type="search"],input:not([type]),textarea')) return true;
    const rules = field.closest('#vacancyForm') ? [...moderationRules, ...vacancyFairnessRules] : moderationRules;
    const issue = rules.find(rule => rule.pattern.test(field.value));
    field.setCustomValidity(issue ? issue.message : '');
    field.classList.toggle('content-blocked', Boolean(issue));
    let warning = field.parentElement?.querySelector(':scope > .field-warning');
    if (issue && !warning) {
      warning = document.createElement('small');
      warning.className = 'field-warning';
      field.insertAdjacentElement('afterend', warning);
    }
    if (warning) {
      warning.textContent = issue ? `⚠ ${issue.message}. Відредагуйте текст.` : '';
      warning.hidden = !issue;
    }
    return !issue;
  }

  function renderRoleFeed() {
    const roleCopy = {
      youth: {
        interests: 'Рекомендації: проєкти · волонтерство · кар’єра',
        ai: 'Події, людей і можливості за твоїми інтересами'
      },
      employer: {
        interests: 'Рекомендації: молодіжні тренди · таланти · партнерства',
        ai: 'Кандидати, професійні спільноти й корисні події'
      },
      center: {
        interests: 'Рекомендації: досвід центрів · події · потреби спільнот',
        ai: 'Практики центрів, активних учасників і партнерства'
      }
    }[state.role];
    $('#feedPersonalization').textContent = roleCopy.interests;
    $('[data-feed-ai-copy]').textContent = roleCopy.ai;
    const composerCopy = {
      youth: ['Поділися ідеєю, результатом або запитом…', 'Твоя історія'],
      employer: ['Розкажіть про команду, вакансію або корисну можливість…', 'Історія бренду'],
      center: ['Розкажіть про подію, результат або потребу громади…', 'Історія центру']
    }[state.role];
    $('#feedComposerForm textarea').placeholder = composerCopy[0];
    $('[data-action="create-story"] small').textContent = composerCopy[1];
    const feed = $('#socialFeed');
    const posts = $$('.social-post', feed);
    posts.forEach(post => {
      post.classList.toggle('priority-post', post.dataset.feedRole === state.role);
      const waitingForLoad = post.hasAttribute('data-extra-feed') && !state.feedExtrasLoaded;
      const filteredByMode = state.feedMode === 'following' && post.dataset.feedKind !== 'following';
      post.hidden = waitingForLoad || filteredByMode;
    });
    posts.sort((a, b) => Number(b.dataset.feedRole === state.role) - Number(a.dataset.feedRole === state.role))
      .forEach(post => feed.append(post));
    $$('[data-feed-mode]').forEach(button => button.classList.toggle('active', button.dataset.feedMode === state.feedMode));
  }

  function saveMiraMemory(patch = {}) {
    state.miraMemory = { ...state.miraMemory, ...patch };
    state.miraMemory.interests = [...new Set(state.miraMemory.interests || [])].slice(-6);
    localStorage.setItem('mriia_mira_memory', JSON.stringify(state.miraMemory));
    updateMiraMemoryUI();
  }

  function rememberInterest(value) {
    const interest = String(value || '').trim().slice(0, 40);
    if (!interest) return;
    saveMiraMemory({ interests: [...(state.miraMemory.interests || []), interest], actions: state.miraMemory.actions + 1 });
  }

  function updateMiraMemoryUI() {
    const role = state.role === 'center' ? 'молодіжний центр' : state.role === 'employer' ? 'роботодавець' : 'користувач';
    const interests = state.miraMemory.interests?.length ? state.miraMemory.interests.join(' · ') : 'інтереси ще знайомляться';
    if ($('#miraMemorySummary')) $('#miraMemorySummary').textContent = `${state.userName || 'Ім’я ще не вказано'} · ${role} · ${interests}`;
    if ($('#miraGreeting')) $('#miraGreeting').textContent = state.miraMemory.actions
      ? `Я пам’ятаю наш попередній контекст і говоритиму ${state.miraMemory.tone}. Що зараз найважливіше?`
      : 'Давай знайомитися поступово. Я підлаштую підказки під твою роль, інтереси й темп.';
  }

  function updatePersonalization() {
    const name = state.userName || 'друже';
    $$('[data-user-name]').forEach(el => el.textContent = name);
    $$('[data-user-initial]').forEach(el => el.textContent = name.slice(0, 1).toUpperCase() || 'М');
    updateProfileEmployment();
  }

  function updateProfileEmployment() {
    const employment = state.employment || 'Не вказано';
    const affiliation = [state.position, state.affiliation].filter(Boolean).join(' · ') || 'Додай статус, заклад або організацію';
    if ($('#profileEmployment')) $('#profileEmployment').textContent = employment;
    if ($('#profileAffiliation')) $('#profileAffiliation').textContent = affiliation;
    $$('[data-role-position]').forEach(el => el.textContent = state.position || (state.role === 'center' ? 'Керівник/ця молодіжного центру' : 'HR-менеджер/ка'));
    $$('[data-role-organization]').forEach(el => el.textContent = state.affiliation || (state.role === 'center' ? 'Полтавський обласний молодіжний центр' : 'Агенція «Вектор»'));
  }

  function profilePayload() {
    return { name: state.userName, role: state.welcomeRole || state.role, employment: state.employment, position: state.position, affiliation: state.affiliation, profileVisible: state.profileVisible };
  }

  function prepareRegistration(role = state.welcomeRole || state.role) {
    const form = $('#registrationForm');
    form.elements.display_name.value = state.userName || '';
    form.elements.profile_role.value = role;
    form.elements.employment.value = state.employment || (role === 'youth' ? '' : 'Працюю');
    form.elements.position.value = state.position || (role === 'center' ? 'Керівник/ця молодіжного центру' : role === 'employer' ? 'HR-менеджер/ка' : '');
    form.elements.affiliation.value = state.affiliation || (role === 'center' ? 'Полтавський обласний молодіжний центр' : role === 'employer' ? 'Агенція «Вектор»' : '');
    form.elements.profile_visibility.checked = state.profileVisible;
    updateRegistrationRequirements(role);
  }

  function updateRegistrationRequirements(role = $('#registrationRole')?.value || 'youth') {
    const required = role === 'center' || role === 'employer';
    const form = $('#registrationForm');
    form.elements.employment.required = required;
    form.elements.position.required = required;
    form.elements.affiliation.required = required;
    $('#employmentOptional').textContent = required ? 'обов’язково' : 'необов’язково';
    $('#positionField').querySelector('small').textContent = required ? 'обов’язково' : 'необов’язково';
    $('#affiliationField').querySelector('small').textContent = required ? 'обов’язково' : 'необов’язково';
    $('#registrationIntro').textContent = required
      ? 'Для підтвердженого представника організації посада, зайнятість і місце роботи обов’язкові.'
      : 'Статус зайнятості, заклад або організація — добровільні. Їх можна не показувати у профілі.';
  }

  function positionFloatingUi() {
    const shell = $('.app-shell');
    const fab = $('.mira-fab');
    const nav = $('.bottom-nav');
    if (!shell || !fab || !nav) return;
    const rect = shell.getBoundingClientRect();
    document.documentElement.style.setProperty('--app-right-gap', `${Math.max(12, window.innerWidth - rect.right + 18)}px`);
    document.documentElement.style.setProperty('--bottom-nav-height', `${Math.ceil(nav.getBoundingClientRect().height)}px`);
  }

  function showDemoAction({ icon = '✦', eyebrow = 'ІНТЕРАКТИВНЕ ДЕМО', title, body }) {
    $('#demoActionIcon').textContent = icon;
    $('#demoActionEyebrow').textContent = eyebrow;
    $('#demoActionTitle').textContent = title;
    $('#demoActionBody').innerHTML = body;
    openModal('#demoActionModal');
  }

  function openStory(id) {
    const story = stories[id] || stories.center;
    state.currentStory = id in stories ? id : 'center';
    $('#storyAvatar').textContent = story.avatar;
    $('#storyAvatar').className = `post-avatar ${story.avatarClass}`;
    $('#storyAuthor').textContent = story.author;
    $('#storyTime').textContent = story.time;
    $('#storyIcon').textContent = story.icon;
    $('#storyTitle').textContent = story.title;
    $('#storyText').textContent = story.text;
    $('#storyCta').textContent = story.cta;
    openModal('#storyModal');
    analytics.send('story_open', { story: state.currentStory });
  }

  function openStoryProfile() {
    const story = stories[state.currentStory] || stories.center;
    if (story.profile) {
      closeModals();
      showScreen(story.profile);
      return;
    }
    showDemoAction({ icon: story.avatar, eyebrow: story.profileType === 'person' ? 'ПРОФІЛЬ КОРИСТУВАЧА' : 'ПРОФІЛЬ ОРГАНІЗАЦІЇ', title: story.author, body: `<article><strong>${story.details}</strong><small>Підтверджений демопрофіль. Публічні контакти відкриваються лише за згодою.</small></article><article><strong>${story.title}</strong><small>${story.text}</small></article>` });
  }

  function showWelcomeStep(step) {
    $$('[data-welcome-step]').forEach(section => section.hidden = section.dataset.welcomeStep !== step);
  }

  function roleLabel(role) {
    return role === 'center' ? 'керівника молодіжного центру' : role === 'employer' ? 'роботодавця' : 'користувача';
  }

  function chooseWelcomeRole(role) {
    state.welcomeRole = role;
    state.role = role;
    document.body.dataset.role = role;
    $('[data-welcome-step="role"]')?.querySelectorAll('[data-welcome-role]').forEach(button => button.classList.toggle('selected', button.dataset.welcomeRole === role));
    $('#welcomeRoleCopy').textContent = `Я підготувала маршрут для ${roleLabel(role)}. Обери: пройти спокійне навчання або досліджувати все самостійно.`;
    updatePersonalization();
    saveMiraMemory({ name: state.userName, role });
    showWelcomeStep('ready');
  }

  function enterDemo(withTour = true) {
    localStorage.setItem('mriia_demo_profile', JSON.stringify(profilePayload()));
    $('#welcomeFlow').hidden = true;
    switchRole(state.welcomeRole);
    updatePersonalization();
    if (withTour) setTimeout(startTour, 350);
    else toast('Досліджуй у своєму темпі. Я поруч у кнопці «Запитати Міру» 💛');
    analytics.send('welcome_complete', { role: state.welcomeRole, tour: withTour });
  }

  const analytics = {
    async send(event, data = {}) {
      const payload = {
        event,
        data,
        session_id: state.sessionId,
        screen: state.currentScreen,
        ts: new Date().toISOString(),
        viewport: `${window.innerWidth}x${window.innerHeight}`
      };
      if (!CAN_USE_API) return;
      try {
        const response = await fetch(`${API_BASE}/track.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
          keepalive: true
        });
        if (!response.ok) throw new Error('analytics unavailable');
      } catch {
        const queued = JSON.parse(localStorage.getItem('mriia_analytics_queue') || '[]');
        queued.push(payload);
        localStorage.setItem('mriia_analytics_queue', JSON.stringify(queued.slice(-100)));
      }
    }
  };

  function toast(message) {
    const el = $('#toast');
    el.textContent = message;
    el.classList.add('show');
    clearTimeout(toast.timer);
    toast.timer = setTimeout(() => el.classList.remove('show'), 2300);
  }

  function showScreen(name) {
    $$('.screen').forEach(el => el.classList.toggle('active', el.dataset.screen === name));
    $$('[data-nav]').forEach(el => el.classList.toggle('active', el.dataset.nav === name));
    state.currentScreen = name;
    if (name === 'home') renderRoleFeed();
    if (name === 'employer-talent') renderCandidates();
    $('.app-shell').scrollTo({ top: 0, behavior: 'smooth' });
    requestAnimationFrame(positionFloatingUi);
    analytics.send('screen_view', { name });
  }

  function switchRole(role) {
    state.role = role;
    document.body.dataset.role = role;
    $$('[data-role]').forEach(button => button.classList.toggle('active', button.dataset.role === role));
    $$('[data-youth-nav]').forEach(button => button.hidden = role !== 'youth');
    $$('[data-center-nav]').forEach(button => button.hidden = role !== 'center');
    $$('[data-employer-nav]').forEach(button => button.hidden = role !== 'employer');
    renderRoleFeed();
    saveMiraMemory({ name: state.userName, role });
    showScreen('home');
    requestAnimationFrame(positionFloatingUi);
    analytics.send('role_switch', { role });
  }

  function openModal(id) {
    const modal = $(id);
    const active = document.activeElement;
    if (!active?.closest?.('.modal')) modalReturnFocus = active instanceof HTMLElement ? active : null;
    modal.inert = false;
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    modal.scrollTop = 0;
    const sheet = $('.modal-sheet', modal);
    if (sheet) sheet.scrollTop = 0;
    const focusTarget = $('[data-action="close-modal"]', modal) || $('button, input, textarea, select, [tabindex]:not([tabindex="-1"])', modal);
    focusTarget?.focus({ preventScroll: true });
    $$('.modal').forEach(el => {
      if (el === modal) return;
      el.classList.remove('open');
      el.setAttribute('aria-hidden', 'true');
      el.inert = true;
    });
    document.body.style.overflow = 'hidden';
  }

  function closeModals() {
    const fallback = modalReturnFocus?.isConnected ? modalReturnFocus : $('.brand');
    fallback?.focus({ preventScroll: true });
    $$('.modal').forEach(el => {
      el.classList.remove('open');
      el.setAttribute('aria-hidden', 'true');
      el.inert = true;
    });
    modalReturnFocus = null;
    document.body.style.overflow = '';
  }

  async function shareDemo(title = 'Молодіжна можливість') {
    const data = { title, text: 'Дивись можливість у молодіжній платформі', url: location.href };
    try {
      if (navigator.share) await navigator.share(data);
      else { await navigator.clipboard.writeText(location.href); toast('Посилання скопійовано'); }
      analytics.send('share_demo', { title });
    } catch (error) {
      if (error?.name !== 'AbortError') toast('Не вдалося відкрити поширення');
    }
  }

  async function startCamera() {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
      $('#scannerVideo').srcObject = stream;
      await $('#scannerVideo').play();
      toast('Камеру увімкнено');
      analytics.send('qr_camera_start');
    } catch {
      toast('Камера недоступна — використай симуляцію');
      analytics.send('qr_camera_denied');
    }
  }

  function stopCamera() {
    const stream = $('#scannerVideo')?.srcObject;
    stream?.getTracks().forEach(track => track.stop());
    if ($('#scannerVideo')) $('#scannerVideo').srcObject = null;
  }

  const exploreItems = [
    { id: 'lab', icon: '⚡', title: 'Лабораторія власних проєктів', meta: '22 серпня · Полтава', tags: 'проєкти команда навчання', description: 'Практична зустріч, де ідея перетворюється на перший план дій і команду.' },
    { id: 'media', icon: '🤝', title: 'Медіаволонтерство: перша місія', meta: '24 серпня · онлайн', tags: 'волонтерство медіа відео', description: 'Коротка волонтерська місія з наставником і підтвердженими годинами.' },
    { id: 'career-talk', icon: '↗', title: 'Кар’єрна розмова без краваток', meta: '27 серпня · Миргород', tags: 'кар’єра робота консультація', description: 'Жива розмова про першу роботу, резюме та співбесіду без формальностей.' },
    { id: 'microgrant', icon: '₴', title: 'Мікрогрант: від ідеї до заявки', meta: '29 серпня · онлайн', tags: 'проєкти гранти фінансування', description: 'Розбір простої заявки, бюджету й критеріїв відбору молодіжних ініціатив.' },
    { id: 'wellbeing', icon: '🌿', title: 'Група підтримки «Видихни»', meta: 'Щосереди · анонімно', tags: 'добробут психолог підтримка здоров’я', description: 'Безпечна онлайн-зустріч: можна говорити, слухати або залишатися анонімно.' },
    { id: 'nocode', icon: '{ }', title: 'No-code: створи свій перший сайт', meta: '31 серпня · Кременчук', tags: 'кар’єра технології навчання проєкти', description: 'Практикум для початківців без вимоги знати програмування.' },
    { id: 'inclusive-space', icon: '◉', title: 'Інклюзивний молодіжний простір', meta: '2 вересня · Полтава', tags: 'добробут інклюзивність доступність', description: 'Знайомство з доступним простором, послугами та фахівцями поруч.' },
    { id: 'mentoring', icon: '∞', title: 'Менторство «молодь для молоді»', meta: '5 вересня · гібридно', tags: 'кар’єра проєкти менторство розвиток', description: 'Зустріч із ментором і персональний план наступних кроків.' }
  ];

  function renderExplore(query = '') {
    const normalized = query.trim().toLowerCase();
    const items = exploreItems.filter(item => `${item.title} ${item.meta} ${item.tags} ${item.description}`.toLowerCase().includes(normalized));
    $('#exploreResults').innerHTML = items.length
      ? items.map(item => `<button class="explore-result-card" type="button" data-action="open-explore-result" data-result-id="${item.id}"><span class="result-dot">${item.icon}</span><span><strong>${item.title}</strong><small>${item.meta}</small></span><b>Детальніше →</b></button>`).join('')
      : '<article><span class="result-dot">⌕</span><div><h4>Нічого не знайдено</h4><p>Спробуй інше слово</p></div></article>';
  }

  function openExploreResult(id) {
    const item = exploreItems.find(entry => entry.id === id);
    if (!item) return;
    state.selectedEvent = { id: item.id, title: item.title, date: item.meta, description: item.description };
    $('#eventModalTitle').textContent = item.title;
    $('#eventModalDate').textContent = item.meta.toUpperCase();
    $('#eventModalDescription').textContent = item.description;
    $('#eventModalDetails').innerHTML = `<span>🗓 ${item.meta}</span><span>👤 Перевірений організатор</span><span>♿ Умови доступності в описі</span>`;
    openModal('#eventModal');
    analytics.send('explore_result_open', { result_id: item.id });
  }

  function openTeamOpportunity(button) {
    state.pendingTeamInterestButton = button;
    const isBookExchange = Boolean(button.closest('[data-post-id="user-project"]'));
    const content = isBookExchange
      ? { icon: '📚', title: 'Команда книгообміну', organizer: 'Оля Марченко · громадська ініціатива', where: 'Полтава · гібридно', schedule: '2–4 години на тиждень до 15 вересня', roles: 'Дизайн афіші · коротке відео · комунікація', task: 'Допомогти оформити афішу, зняти короткий Reels і розповісти про книгообмін.' }
      : { icon: '🌿', title: 'Екофестиваль у громаді', organizer: 'Ініціативна група GreenHub', where: 'Полтавська громада · офлайн', schedule: 'Підготовка у серпні · подія 7 вересня', roles: 'Дизайнер/ка · фотограф/ка · координатор/ка партнерств', task: 'Разом підготувати екологічний фестиваль, інформаційні матеріали й партнерства.' };
    showDemoAction({
      icon: content.icon,
      eyebrow: 'МОЖЛИВІСТЬ ДОЛУЧИТИСЯ',
      title: content.title,
      body: `<article><strong>Хто організовує</strong><small>${content.organizer}</small></article><article><strong>Де й коли</strong><small>${content.where}<br>${content.schedule}</small></article><article><strong>Кого шукають</strong><small>${content.roles}</small></article><article><strong>Що потрібно робити</strong><small>${content.task}</small></article><article><strong>Як відбувається знайомство</strong><small>Спочатку надсилається безпечний запит. Контакти відкриються лише після взаємної згоди.</small></article><button class="primary" type="button" data-action="confirm-team-interest">Надіслати запит на знайомство</button>`
    });
  }

  function openEventCard(card) {
    const post = card.closest('.social-post');
    const story = card.closest('.story-sheet');
    const title = $('h4', card)?.textContent || $('b', card)?.textContent || $('b', post || card)?.textContent || $('h2', story || card)?.textContent || 'Молодіжна можливість';
    const date = $('.meta span', card)?.textContent || $('small', card)?.textContent || $('.post-media small', post || card)?.textContent || 'Незабаром';
    const description = $('.event-body p', card)?.textContent || $('p', post || card)?.textContent || 'Відкрий нову можливість і зроби наступний крок.';
    state.selectedEvent = { id: card.dataset.event, title, date, description };
    $('#eventModalTitle').textContent = title;
    $('#eventModalDate').textContent = date.toUpperCase();
    $('#eventModalDescription').textContent = description;
    $('#eventModalDetails').innerHTML = '<span>🕒 17:00–20:00</span><span>📍 Молодіжний центр</span><span>♿ Простір доступний</span>';
    analytics.send('event_open', { event_id: card.dataset.event });
    openModal('#eventModal');
  }

  function saveSelectedEvent() {
    if (!state.selectedEvent) return;
    if (!state.savedEvents.some(item => item.id === state.selectedEvent.id)) state.savedEvents.push(state.selectedEvent);
    $('#savedEventList').innerHTML = state.savedEvents.map(item => `<article><span class="status planned">ЗБЕРЕЖЕНО</span><div><h4>${item.title}</h4><p>${item.date}</p></div><button data-action="open-saved-event" data-saved-id="${item.id}">›</button></article>`).join('');
    analytics.send('event_saved', { event_id: state.selectedEvent.id });
    toast('Збережено в «Мій маршрут»');
  }

  function miraReply(question) {
    const text = question.toLowerCase();
    const name = state.userName || 'Друже';
    if (/робот|ваканс|стаж/.test(text)) return `<strong>${name}, розумію. Давай без зайвого тиску:</strong><br>• переглянемо вакансії зі збігом навичок<br>• за потреби запишемося на консультацію<br>• підсилимо профіль підтвердженим досвідом`;
    if (/центр|захід|qr|звіт/.test(text)) return `<strong>${name}, я поруч із командою центру:</strong><br>• допоможу створити демозахід<br>• проведу через QR check-in<br>• поясню аналітику людською мовою`;
    if (/люд|команд|друз/.test(text)) return `<strong>${name}, почнімо з безпечного знайомства:</strong><br>• знайдемо спільноту за інтересами<br>• подивимося відкриті команди<br>• контакт відкриється лише за взаємною згодою`;
    if (/псих|важко|допомог|самот/.test(text)) return `<strong>${name}, я чую тебе й можу побути поруч.</strong><br>Ти можеш розповідати лише те, що комфортно. Я не замінюю психолога, але допоможу сформулювати думки, знайти перевірену підтримку або кризовий контакт, якщо стане небезпечно.`;
    return `<strong>${name}, добре, що ти написав/ла.</strong><br>Я можу допомогти знайти можливість, людину, подію або просто розкласти ситуацію на маленькі кроки. З чого хочеш почати?`;
  }

  function assessCrisis(message) {
    const text = String(message || '').toLowerCase();
    const immediate = /(покончи(ти|ть)|самогуб|суїцид|вбити\s+(себе|когось|його|її)|убить\s+(себя|кого)|заріж|застрел|стриба(ти|ну)\s+з|поріжу\s+(себе|когось)|не\s+хочу\s+жити|не\s+хочу\s+жить|мене\s+(б'ють|бьют|душать|ріжуть)|на\s+мене\s+напали|у\s+нього\s+зброя|у\s+нее\s+оружие)/iu;
    const concern = /(насильств|домашн.*насильств|ґвалт|изнасил|погрож|угрожа|пересліду|сталкер|булінг|буллят|цькують|знущають|б'ють|бьют|небезпечно|опасно|примушують|шантаж)/iu;
    if (immediate.test(text)) return 'immediate';
    if (concern.test(text)) return 'concern';
    return '';
  }

  function openCrisisFlow(level = 'concern') {
    $('#crisisTitle').textContent = level === 'immediate'
      ? 'Ти зараз у безпосередній небезпеці?'
      : 'Давай знайдемо безпечний наступний крок';
    $('#crisisIntro').textContent = level === 'immediate'
      ? 'Якщо загроза відбувається просто зараз, відійди у безпечніше місце, якщо можеш, і звернися до екстреної служби.'
      : 'Тобі не потрібно пояснювати все одразу. Можна обрати перевірений контакт або підготувати коротке повідомлення.';
    $('#crisisModal').dataset.level = level;
    $('#crisisResponse').classList.remove('is-ready');
    $('#crisisResponse').innerHTML = '<strong>У цьому демо виклики й повідомлення не надсилаються.</strong><p>Міра лише покаже сценарій: не створить журнал і не передасть дані.</p>';
    openModal('#crisisModal');
  }

  function showCrisisAction(action, title, copy) {
    const response = $('#crisisResponse');
    response.classList.add('is-ready');
    response.innerHTML = `<strong>${title}</strong><p>${copy}</p><small class="crisis-message-template">Демо: реальний виклик, повідомлення і журналювання не здійснюються.</small>`;
    speakMira(`${title}. ${copy}`);
  }

  function plainSpeech(html) {
    const fragment = document.createElement('div');
    fragment.innerHTML = html;
    return fragment.textContent.replace(/\s+/g, ' ').trim();
  }

  function preferredMiraVoice() {
    const voices = speechSynthesis.getVoices();
    const femaleHints = /(kateryna|polina|oksana|olena|lesya|female|жіноч)/i;
    return voices.find(voice => /^uk([-_]|$)/i.test(voice.lang) && femaleHints.test(voice.name))
      || voices.find(voice => /^uk([-_]|$)/i.test(voice.lang))
      || voices.find(voice => /^ru([-_]|$)/i.test(voice.lang) && femaleHints.test(voice.name))
      || voices.find(voice => femaleHints.test(voice.name))
      || voices[0];
  }

  function speakMira(content) {
    if (!state.miraSound || !('speechSynthesis' in window)) return;
    const text = plainSpeech(content).slice(0, 650);
    if (!text) return;
    speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(text);
    const voice = preferredMiraVoice();
    if (voice) utterance.voice = voice;
    utterance.lang = voice?.lang || 'uk-UA';
    utterance.rate = .96;
    utterance.pitch = 1.08;
    speechSynthesis.speak(utterance);
  }

  function startMiraVoice() {
    const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!Recognition) {
      $('#miraVoiceStatus').textContent = 'Браузер не підтримує розпізнавання';
      return toast('Голосове введення недоступне в цьому браузері');
    }
    if (state.voiceListening) return;
    const recognition = new Recognition();
    recognition.lang = 'uk-UA';
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;
    state.voiceListening = true;
    $('#miraVoiceButton').classList.add('is-listening');
    $('#miraVoiceStatus').textContent = 'Слухаю… говори у своєму темпі';
    recognition.onresult = event => {
      $('#miraQuestion').value = event.results[0][0].transcript;
      $('#miraVoiceStatus').textContent = 'Почула. Готую відповідь…';
      $('#miraForm').requestSubmit();
    };
    recognition.onerror = event => {
      $('#miraVoiceStatus').textContent = event.error === 'not-allowed' ? 'Потрібен дозвіл на мікрофон' : 'Не розчула. Спробуй ще раз';
    };
    recognition.onend = () => {
      state.voiceListening = false;
      $('#miraVoiceButton').classList.remove('is-listening');
      if ($('#miraVoiceStatus').textContent.startsWith('Слухаю')) $('#miraVoiceStatus').textContent = 'Натисни, щоб сказати ще';
    };
    recognition.start();
  }

  function returnToTourMenu() {
    state.tourBranch = '';
    state.tour = 1;
    state.tourAwaitingContinue = false;
    clearInlineTourPraise();
    closeModals();
    $('#onboarding').hidden = false;
    renderTour();
  }

  function updateVacancyQuality() {
    const form = $('#vacancyForm');
    const important = ['title','city','salary_from','conditions','company','responsibilities','requirements','contact_name','cta'];
    const completed = important.filter(name => String(form.elements[name]?.value || '').trim()).length;
    const youthOptions = new FormData(form).getAll('youth_options').length;
    const score = Math.min(100, Math.round((completed / important.length) * 85 + Math.min(youthOptions, 3) * 5));
    $('#vacancyQualityBar').style.width = `${score}%`;
    $('#vacancyQualityText').textContent = score >= 90 ? 'Готова до публікації' : score >= 70 ? 'Майже готова' : 'Варто доповнити';
  }

  function renderVacancyPreview() {
    const form = $('#vacancyForm');
    const data = new FormData(form);
    const preview = $('#vacancyPreview');
    preview.replaceChildren();
    const label = document.createElement('small');
    label.textContent = `${data.get('type')} · ${data.get('format')} · ${data.get('city')}`.toUpperCase();
    const title = document.createElement('strong');
    title.textContent = String(data.get('title') || 'Нова вакансія');
    const salary = document.createElement('p');
    salary.textContent = `${data.get('salary_from') || '—'}–${data.get('salary_to') || '—'} грн · ${data.get('salary_comment') || 'умови оплати уточнюються'}`;
    const conditions = document.createElement('p');
    conditions.textContent = String(data.get('conditions') || 'Умови ще не заповнені');
    const options = document.createElement('span');
    const optionLabels = [...form.querySelectorAll('[name="youth_options"]:checked + span')].map(el => el.textContent);
    options.textContent = optionLabels.length ? optionLabels.join(' · ') : 'Додайте умови для старту кар’єри';
    preview.append(label, title, salary, conditions, options);
    preview.hidden = false;
    preview.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    analytics.send('vacancy_preview_demo');
  }

  function tourSteps() {
    const intro = { icon: '👋', title: `Не поспішай, ${state.userName || 'друже'}!`, text: 'Я — Міра. Поясню все послідовно, а ти сам вирішуватимеш, коли переходити до наступного кроку.' };
    const finish = { icon: '🏆', title: 'Ти чудово впорався!', text: 'Тепер досліджуй платформу самостійно. Якщо потрібна підказка — кнопка «Запитати Міру» завжди поруч.' };
    const mira = { icon: '✦', title: 'Спробуй запитати Міру', text: 'Ця кнопка відкриває AI-помічницю. Натисни її — я покажу, що можна запитати.', target: '.mira-fab', praise: 'Молодець! Ти відкрив AI-навігатор. Тут є готові запити й поле для власного питання.' };
    const aiSteps = [
      { icon: '👥', title: 'Пошук команди', text: 'Цей пункт допомагає знайти людей зі схожими інтересами. Натисни й подивися приклад відповіді.', target: '.prompt-list [data-prompt="team"]', praise: 'Супер! Міра запропонувала команди й події, де можна знайти однодумців.' },
      { icon: '💛', title: 'Волонтерські можливості', text: 'Тут можна знайти короткі або довготривалі волонтерські місії. Спробуй натиснути.', target: '.prompt-list [data-prompt="volunteer"]', praise: 'Чудово! Так Міра підбиратиме волонтерство відповідно до інтересів і вільного часу.' },
      { icon: '🫶', title: 'Безпечна допомога', text: 'Цей пункт веде до перевіреної психологічної допомоги, зокрема анонімної. Натисни, щоб побачити приклад.', target: '.prompt-list [data-prompt="help"]', praise: 'Молодець! Міра показала безпечні варіанти підтримки без зайвих запитань.' },
      { icon: '⌨', title: 'Можна запитати своїми словами', text: 'У цьому полі користувач може написати власне питання. Спробуй пізніше будь-який запит — Міра залишатиметься поруч.', target: '#miraQuestion', requireAction: false }
    ];
    const socialSteps = [
      { icon: '⌂', title: 'Це твоя головна стрічка', text: 'Незалежно від ролі все починається із соціальної мережі. Тут поєднані дописи підписок і персональні рекомендації Міри.', target: `[data-${state.role === 'youth' ? 'youth' : state.role}-nav][data-nav="home"]`, praise: 'Розумничка! Тепер ти на головній — стрічка вже підлаштована під твою роль.' },
      { icon: '◉', title: 'Тиць на історію', text: 'Кольорове коло означає нову історію від сторінки, на яку ти підписаний. Натисни — вона відкриється на весь екран.', target: '.stories [data-action="open-story"]', praise: 'Молодець! Так можна швидко переглядати новини людей, центрів і організацій.' },
      { icon: '＋', title: 'Тут створюється допис', text: 'Напиши ідею, результат, запит про допомогу або нову можливість. Для центру й роботодавця автором буде їхня сторінка.', target: '#feedComposerForm textarea', requireAction: false },
      { icon: '◎', title: 'Підписки або рекомендації', text: '«Для тебе» змішує цікаве за вподобаннями, а «Підписки» залишає тільки сторінки, які ти обрав. Тиць «Підписки».', target: '[data-feed-mode="following"]', praise: 'Супер! Тепер у стрічці лише ті, на кого ти підписаний.' },
      { icon: '♡', title: 'Покажи, що допис корисний', text: 'Тиць на сердечко. Реакції допомагають автору й навчають Міру точніше підбирати рекомендації.', target: '[data-screen="home"] .social-post [data-action="like-post"]', praise: 'Розумничка! Реакцію додано, а рекомендації стали точнішими.' },
      { icon: '○', title: 'Обговорюй безпечно', text: 'Тиць на коментар. Там можна поставити питання або підтримати автора; Міра перевірить текст перед публікацією.', target: '[data-screen="home"] .social-post [data-action="comment-post"]', praise: 'Молодець! Тут можна вести змістовне й безпечне обговорення.' },
      { icon: '↗', title: 'Поділися можливістю', text: 'Ця кнопка відкриває системне поширення або копіює посилання, щоб запросити друзів чи команду.', target: '[data-screen="home"] .social-post [data-action="share-post"]', requireAction: false },
      { icon: '▱', title: 'Збережи на потім', text: 'Тиць на закладку — допис залишиться у збережених, навіть якщо зараз немає часу діяти.', target: '[data-screen="home"] .social-post [data-action="save-post"]', praise: 'Чудово! Тепер корисний допис не загубиться.' }
    ];
    const menuChoice = { icon: '☰', title: 'Куди хочеш зазирнути?', text: 'Ось головне меню. Тиць на будь-який розділ, який цікавий саме тобі. Я відкрию його й поясню все вже там.', target: '.bottom-nav', chooseNav: true, praise: 'Чудовий вибір! Розділ відкрито. Зараз покажу найважливіші можливості — а ти вирішиш, що спробувати.' };
    const branchMaps = {
      youth: {
        home: socialSteps.slice(1),
        explore: [
          { icon: '⌕', title: 'Шукай своїми словами', text: 'Тут можна написати тему, навичку або назву події. Результати зміняться одразу — без окремої кнопки.', target: '#searchInput', requireAction: false },
          { icon: '✦', title: 'Або обери інтерес', text: 'Якщо не знаєш, що написати, тиць на готову тему — волонтерство, кар’єра, добробут чи проєкти.', target: '.interest-grid', requireAction: false },
          { icon: '◎', title: 'Ось персональні результати', text: 'Міра поєднує твій запит, інтереси, місто й доступний формат, щоб показати доречні можливості.', target: '#exploreResults', requireAction: false }
        ],
        community: [
          { icon: '👥', title: 'Спочатку переглянь подробиці', text: 'Тиць «Детальніше»: побачиш організатора, ролі, завдання, місце й тривалість. Запит надсилається лише окремою кнопкою після перегляду.', target: '[data-screen="community"] [data-action="open-team-details"]', praise: 'Розумничка! Тепер усі умови перед очима, а рішення про знайомство залишається за тобою.' },
          { icon: '👋', title: 'Почни з простого привітання', text: 'Ця кнопка надсилає легке «Привіт». Людина сама вирішить, чи починати розмову.', target: '.people-list [data-action="wave"]', praise: 'Молодець! Саме так знайомство починається без тиску.' }
        ],
        profile: [
          { icon: '↗', title: 'Тут видно твій прогрес', text: 'Участі, волонтерські години та навички складаються у зрозумілий маршрут розвитку.', target: '[data-screen="profile"] .progress-card', requireAction: false },
          { icon: '✓', title: 'Результати можна підтвердити', text: 'Тиць на сертифікат — його можна перевірити й показати університету або роботодавцю.', target: '[data-action="open-certificate"]', praise: 'Супер! Це вже частина твого цифрового портфоліо.' }
        ]
      },
      employer: {
        home: socialSteps.slice(1),
        'employer-home': [
          { icon: '✦', title: 'Міра вже знайшла збіги', text: 'Рекомендації формуються за навичками, містом і умовами активної вакансії.', target: '#employerRecommendations', requireAction: false },
          { icon: '＋', title: 'Хочеш створити вакансію?', text: 'Тиць сюди — відкриється детальна, але зрозуміла форма. Я поясню кожен блок.', target: '[data-screen="employer-home"] [data-action="create-vacancy"]', praise: 'Чудово! Форма відкрита — заповнюй у своєму темпі.' }
        ],
        'employer-vacancies': [
          { icon: '＋', title: 'Створи нову вакансію', text: 'Тиць сюди. Демо вже містить приклад, тому можна спокійно дослідити всі поля.', target: '[data-screen="employer-vacancies"] [data-action="create-vacancy"]', praise: 'Молодець! Тепер роздивімося форму очима потенційного кандидата.' },
          { icon: '1', title: 'Спочатку — зрозуміла основа', text: 'Назва, напрям, місто, формат, графік і потрібний досвід. Нічого зайвого.', target: '#vacancyBasicsGuide', requireAction: false },
          { icon: '2', title: 'Потім — чесна оплата', text: 'Діапазон, податки, бонуси й реальні умови. Прозорість підвищує довіру.', target: '#vacancyPayGuide', requireAction: false },
          { icon: '3', title: 'Що робити й що вміти', text: 'Задачі та вимоги розділені, щоб кандидат швидко зрозумів очікування.', target: '#vacancyDetailsGuide', requireAction: false },
          { icon: '4', title: 'Умови для старту', text: 'Без досвіду, наставник, навчання та доступність — позначай лише те, що справді забезпечуєш.', target: '#vacancyInclusiveGuide', requireAction: false },
          { icon: '👁', title: 'Подивися очима кандидата', text: 'Тиць на попередній перегляд. Дані ще нікуди не надсилаються.', target: '#vacancyForm [data-action="preview-vacancy"]', praise: 'Розумничка! Тепер легко помітити, чого бракує у вакансії.' }
        ],
        'employer-talent': [
          { icon: '⌕', title: 'Шукай серед 100 демопрофілів', text: 'Введи навичку, місто або напрям — список відфільтрується одразу.', target: '#talentSearch', requireAction: false },
          { icon: '◎', title: 'Або використай готові фільтри', text: '«Рекомендовані» враховують твою вакансію, інші кнопки звужують пошук за напрямом.', target: '#candidateFilters', requireAction: false },
          { icon: '↗', title: 'Запроси без порушення приватності', text: 'Тиць «Запросити». Контакт відкриється лише після згоди потенційного кандидата.', target: '#employerTalentList [data-action="invite-talent"]', praise: 'Молодець! Демозапрошення надіслано етично й без розкриття приватних даних.' }
        ],
        'employer-profile': [
          { icon: '◉', title: 'Це публічне обличчя організації', text: 'Умови, довіра й підтверджені дані допомагають кандидату зрозуміти, хто ви.', target: '[data-screen="employer-profile"] .public-center-title', requireAction: false },
          { icon: '↗', title: 'Профілем можна поділитися', text: 'Тиць сюди, щоб надіслати перевірену сторінку у соцмережі або месенджер.', target: '[data-action="share-employer"]', requireAction: false }
        ]
      },
      center: {
        home: socialSteps.slice(1),
        'center-home': [
          { icon: '＋', title: 'Додай захід', text: 'Тиць сюди — відкриється коротка форма публікації події.', target: '[data-screen="center-home"] [data-action="create-event"]', praise: 'Чудово! Тут створюється подія, а реєстрації та статистика підтягнуться автоматично.' },
          { icon: '▣', title: 'QR check-in без списків', text: 'Закрий форму, коли будеш готовий, і тиць на QR. Камеру можна замінити безпечною симуляцією.', target: '[data-screen="center-home"] [data-action="open-scanner"]', closeModalBefore: true, screen: 'center-home', praise: 'Розумничка! Тут адміністратор підтверджує участь за кілька секунд.' },
          { icon: '≋', title: 'Аналітика формується сама', text: 'Тиць сюди — побачиш шлях від перегляду до реальної участі.', target: '[data-screen="center-home"] [data-action="center-analytics"]', closeModalBefore: true, screen: 'center-home', praise: 'Молодець! Це готові дані для команди, звітності й донорів.' }
        ],
        'center-events': [
          { icon: '＋', title: 'Усі події в одному місці', text: 'Тиць, щоб створити нову подію; нижче видно активні, заплановані й чернетки.', target: '[data-screen="center-events"] [data-action="create-event"]', praise: 'Чудово! Форма події відкрита, а приклад уже заповнений.' },
          { icon: '▣', title: 'У день події — один QR', text: 'Ця кнопка відкриває check-in саме для обраного заходу.', target: '[data-screen="center-events"] [data-action="open-scanner"]', closeModalBefore: true, screen: 'center-events', praise: 'Супер! Паперові списки більше не потрібні.' }
        ],
        'center-people': [
          { icon: '⌕', title: 'Знайди потрібну навичку', text: 'Шукай потенційних кандидатів для проєкту, стажування або партнерської можливості.', target: '[data-screen="center-people"] .search-field', requireAction: false },
          { icon: '↗', title: 'Запрошення — лише зі згодою', text: 'Тиць «Запросити». Приватні контакти залишаються закритими до відповіді.', target: '[data-screen="center-people"] [data-action="invite-talent"]', praise: 'Молодець! Це безпечний перший контакт без тиску.' }
        ],
        'center-profile': [
          { icon: '◉', title: 'Так центр бачить спільнота', text: 'Перевірена сторінка показує контакти, результати й доступні можливості.', target: '[data-screen="center-profile"] .public-center-title', requireAction: false },
          { icon: '↗', title: 'Поділися сторінкою', text: 'Тиць сюди, щоб запросити людей без окремої реклами.', target: '[data-action="share-center"]', requireAction: false }
        ]
      }
    };
    const selectedBranch = branchMaps[state.role]?.[state.tourBranch] || [];
    return [intro, menuChoice, ...selectedBranch, finish];
    if (state.role === 'center') return [
      intro,
      ...socialSteps,
      { icon: '⌂', title: 'Огляд центру', text: 'Тут зібрані ключові показники, сьогоднішній захід і швидкі дії. Натисни «Огляд», щоб спробувати.', target: '[data-center-nav][data-nav="center-home"]', praise: 'Добре! Це головна робоча панель молодіжного центру.' },
      { icon: '▣', title: 'Заходи', text: 'У цьому розділі центр створює події, бачить реєстрації та проводить QR check-in. Натисни «Заходи».', target: '[data-center-nav][data-nav="center-events"]', praise: 'Молодець! Тут зберігатимуться всі заходи та їхня статистика.' },
      { icon: '◎', title: 'Люди', text: 'Тут можна працювати з учасниками та знаходити потенційних кандидатів. Спробуй відкрити.', target: '[data-center-nav][data-nav="center-people"]', praise: 'Супер! Це база учасників і талантів — лише з дозволеними даними.' },
      { icon: '◉', title: 'Сторінка центру', text: 'Це публічний профіль із контактами, можливостями й підтвердженою інформацією. Натисни «Сторінка».', target: '[data-center-nav][data-nav="center-profile"]', praise: 'Чудово! Саме так молодь бачитиме перевірену сторінку центру.' },
      mira,
      ...aiSteps,
      { icon: '＋', title: 'Створи перший захід', text: 'Повернімося до кабінету. Натисни підсвічену кнопку — відкриється форма публікації заходу.', target: '[data-screen="center-home"] [data-action="create-event"]', praise: 'Молодець! Форма відкрита. Роздивись поля й продовжуй, коли будеш готовий.', closeModalBefore: true, screen: 'center-home' },
      { icon: '↗', title: 'Опублікуй демозахід', text: 'Поля вже заповнені прикладом. Натисни публікацію — це не створить реальний захід.', target: '#createEventForm button[type="submit"]', praise: 'Супер! Захід з’явився в кабінеті. Саме тут центр керуватиме реєстраціями.' },
      finish
    ];
    if (state.role === 'employer') return [
      intro,
      ...socialSteps,
      { icon: '⌂', title: 'Огляд роботодавця', text: 'Тут рекомендації Міри, швидкі дії та коротка статистика. Натисни «Огляд».', target: '[data-employer-nav][data-nav="employer-home"]', praise: 'Добре! Це головна панель роботодавця.' },
      { icon: '▤', title: 'Вакансії', text: 'Тут створюються вакансії, стажування та перша робота, а також збираються відгуки. Спробуй.', target: '[data-employer-nav][data-nav="employer-vacancies"]', praise: 'Молодець! Тут роботодавець керуватиме всіма оголошеннями.' },
      { icon: '◎', title: 'Таланти', text: 'Цей розділ допомагає знайти потенційних кандидатів за навичками й досвідом. Натисни.', target: '[data-employer-nav][data-nav="employer-talent"]', praise: 'Супер! Тут видно кандидатів, які погодилися бути відкритими до пропозицій.' },
      { icon: '◉', title: 'Профіль організації', text: 'Перевірена сторінка роботодавця підвищує довіру молоді. Відкрий профіль.', target: '[data-employer-nav][data-nav="employer-profile"]', praise: 'Чудово! Тут молодь побачить умови, контакти та підтвердження організації.' },
      mira,
      ...aiSteps,
      { icon: '＋', title: 'Створи вакансію', text: 'Повернімося до кабінету. Натисни, щоб відкрити розширену форму для роботодавця.', target: '[data-screen="employer-home"] [data-action="create-vacancy"]', praise: 'Молодець! Форма відкрита. Спокійно переглянь, які дані побачить потенційний кандидат.', closeModalBefore: true, screen: 'employer-home' },
      { icon: '1', title: 'Основна інформація', text: 'Точна назва, напрям, місто, формат, графік і досвід допомагають правильно показати вакансію молоді.', target: '#vacancyBasicsGuide', requireAction: false },
      { icon: '2', title: 'Прозора оплата', text: 'Вкажіть реальний діапазон, чи це сума після податків, а також бонуси й умови роботи.', target: '#vacancyPayGuide', requireAction: false },
      { icon: '3', title: 'Задачі та вимоги', text: 'Окремо поясніть організацію, щоденні задачі, необхідні навички та що людина отримає від цієї ролі.', target: '#vacancyDetailsGuide', requireAction: false },
      { icon: '4', title: 'Умови для старту кар’єри', text: 'Тут роботодавець чесно позначає: без досвіду, для студентів, із наставником, навчанням чи доступним робочим місцем.', target: '#vacancyInclusiveGuide', requireAction: false },
      { icon: '5', title: 'Контакт і наступний крок', text: 'Кандидат має знати, хто відповість, коли чекати відповідь і що саме потрібно надіслати.', target: '#vacancyContactGuide', requireAction: false },
      { icon: '👁', title: 'Перевір очима кандидата', text: 'Натисни попередній перегляд — побачиш, як ключова інформація виглядатиме для потенційного кандидата.', target: '#vacancyForm [data-action="preview-vacancy"]', praise: 'Молодець! Тепер видно, чи зрозумілі посада, оплата, формат і переваги.' },
      { icon: '↗', title: 'Опублікуй демовакансію', text: 'Форма вже заповнена прикладом. Натисни публікацію — це лише демо.', target: '#vacancyForm button[type="submit"]', praise: 'Супер! Вакансія вже у твоєму кабінеті. Продовжуй, коли будеш готовий.' },
      { icon: '◎', title: 'Знайди потенційних кандидатів', text: 'Натисни «Таланти» внизу — Міра покаже кандидатів із найкращим збігом.', target: '[data-employer-nav][data-nav="employer-talent"]', praise: 'Чудово! Ось кандидати з найкращим збігом. Можеш роздивитися їхні навички.' },
      finish
    ];
    return [
      intro,
      ...socialSteps,
      { icon: '⌕', title: 'Пошук', text: 'Тут можна знайти події, навчання, консультації та можливості за інтересами. Натисни «Пошук».', target: '[data-youth-nav][data-nav="explore"]', praise: 'Молодець! Пошук допоможе швидко знайти потрібну можливість.' },
      { icon: '◉', title: 'Люди', text: 'Цей розділ об’єднує спільноти, команди та безпечні знайомства після спільних активностей. Спробуй.', target: '[data-youth-nav][data-nav="community"]', praise: 'Супер! Тут можна знайти однодумців і команду для власної ідеї.' },
      { icon: '◉', title: 'Профіль', text: 'Тут зберігатимуться участі, сертифікати, досягнення та волонтерські години. Натисни «Профіль».', target: '[data-youth-nav][data-nav="profile"]', praise: 'Чудово! Це цифрове портфоліо розвитку користувача.' },
      mira,
      ...aiSteps,
      { icon: '⚡', title: 'Обери можливість', text: 'Повернімося до стрічки. Натисни на підсвічену картку заходу, щоб побачити подробиці.', target: '[data-screen="home"] [data-event="lab"]', praise: 'Чудовий вибір! Не поспішай: роздивись опис, дату, формат і організатора.', closeModalBefore: true, screen: 'home' },
      { icon: '✓', title: 'Зареєструйся на захід', text: 'Коли будеш готовий, натисни кнопку реєстрації. У демо реальні дані не передаються.', target: '#eventModal [data-action="register-event"]', praise: 'Молодець! Ти перетворив інтерес на реальну дію. Саме для цього створюється платформа.' },
      { icon: '♡', title: 'Взаємодій із дописами', text: 'Постав реакцію, прокоментуй, пошир або збережи допис — як у звичній соцмережі.', target: '[data-screen="home"] [data-action="like-post"]', praise: 'Супер! Реакції допомагають Мірі точніше налаштовувати твою стрічку.' },
      finish
    ];
  }

  function clearTourTarget() {
    state.tourRenderId += 1;
    $$('.tour-active-target').forEach(target => target.classList.remove('tour-active-target'));
    $$('.tour-info-target').forEach(target => target.classList.remove('tour-info-target'));
    state.tourTarget = null;
    const spotlight = $('#tourSpotlight');
    spotlight.hidden = true;
    spotlight.removeAttribute('style');
    const card = $('.onboarding-card');
    card.classList.remove('has-target', 'is-top', 'is-context');
    card.classList.add('is-bottom');
    card.style.removeProperty('left');
    card.style.removeProperty('top');
    card.style.removeProperty('bottom');
    card.style.removeProperty('width');
    card.style.removeProperty('--arrow-left');
  }

  function mountTourCardOverlay() {
    const overlay = $('#onboarding');
    const card = $('.onboarding-card');
    if (card.parentElement !== overlay) overlay.append(card);
    card.hidden = false;
    card.classList.remove('is-inline', 'is-context');
    overlay.hidden = false;
  }

  function mountTourCardInline() {
    const overlay = $('#onboarding');
    const card = $('.onboarding-card');
    const screen = $('.screen.active');
    if (!screen) return mountTourCardOverlay();
    const anchor = screen.querySelector('.feed-heading, .screen-title, .hero-copy');
    card.hidden = false;
    card.classList.add('is-inline');
    card.classList.remove('has-target', 'is-top', 'is-bottom');
    if (anchor) anchor.insertAdjacentElement('afterend', card);
    else screen.prepend(card);
    overlay.hidden = true;
    requestAnimationFrame(() => card.scrollIntoView({ behavior: 'smooth', block: 'center' }));
  }

  function mountTourCardNearTarget(target) {
    const overlay = $('#onboarding');
    const card = $('.onboarding-card');
    let anchor = target;
    if (target.matches('input, textarea, select')) anchor = target.closest('label, .search-field, .form-guide') || target;
    card.hidden = false;
    card.classList.add('is-inline', 'is-context');
    card.classList.remove('has-target', 'is-top', 'is-bottom');
    anchor.insertAdjacentElement('beforebegin', card);
    target.classList.add('tour-info-target');
    overlay.hidden = true;
    requestAnimationFrame(() => card.scrollIntoView({ behavior: 'smooth', block: 'center' }));
  }

  function positionTourTarget() {
    if (!state.tourActive || !state.tourTarget) return;
    const rect = state.tourTarget.getBoundingClientRect();
    const pad = 7;
    const spotlight = $('#tourSpotlight');
    spotlight.hidden = false;
    spotlight.style.left = `${Math.max(5, rect.left - pad)}px`;
    spotlight.style.top = `${Math.max(5, rect.top - pad)}px`;
    spotlight.style.width = `${Math.min(innerWidth - 10, rect.width + pad * 2)}px`;
    spotlight.style.height = `${rect.height + pad * 2}px`;
    const card = $('.onboarding-card');
    card.classList.add('has-target');
    const cardWidth = Math.min(innerWidth <= 480 ? 306 : 330, innerWidth - 24);
    card.style.width = `${cardWidth}px`;
    const spaceBelow = innerHeight - rect.bottom;
    const spaceAbove = rect.top;
    const placeBelow = spaceBelow >= card.offsetHeight + 24 || (spaceBelow >= spaceAbove && rect.height < innerHeight * .45);
    const left = Math.max(12, Math.min(innerWidth - cardWidth - 12, rect.left + rect.width / 2 - cardWidth / 2));
    let top = placeBelow ? Math.min(innerHeight - card.offsetHeight - 12, rect.bottom + 16) : Math.max(12, rect.top - card.offsetHeight - 16);
    if (rect.height >= innerHeight * .45) top = rect.top >= innerHeight / 2 ? 12 : Math.max(12, innerHeight - card.offsetHeight - 82);
    card.style.left = `${left}px`;
    card.style.top = `${top}px`;
    card.style.bottom = 'auto';
    card.style.setProperty('--arrow-left', `${Math.max(28, Math.min(cardWidth - 28, rect.left + rect.width / 2 - left))}px`);
    card.classList.toggle('is-bottom', placeBelow && rect.height < innerHeight * .45);
    card.classList.toggle('is-top', (!placeBelow) && rect.height < innerHeight * .45);
  }

  function renderTour() {
    clearTourTarget();
    const renderId = state.tourRenderId;
    const steps = tourSteps();
    const step = steps[state.tour];
    if (step.closeModalBefore) closeModals();
    if (step.screen && state.currentScreen !== step.screen) showScreen(step.screen);
    $('.onboarding-icon').textContent = step.icon;
    $('#tourStep').textContent = state.tour + 1;
    $('#tourTotal').textContent = steps.length;
    $('#tourTitle').textContent = step.title;
    $('#tourText').textContent = step.text;
    $('.onboarding-progress i').style.width = `${((state.tour + 1) / steps.length) * 100}%`;
    const next = $('[data-action="next-tour"]');
    const skip = $('[data-action="skip-tour"]');
    next.hidden = Boolean(step.target && step.requireAction !== false);
    next.textContent = state.tour === steps.length - 1 ? 'Завершити навчання' : state.tour === 0 ? 'Почати спокійний огляд' : 'Продовжити';
    skip.textContent = state.tour === steps.length - 1 && state.tourBranch ? 'Обрати інший розділ' : 'Вийти з навчання';
    const hint = $('#tourTapHint');
    hint.hidden = !step.target;
    hint.innerHTML = step.requireAction === false ? '<span>👀</span> Роздивись підсвічений розділ' : '<span>☝</span> Спробуй цю дію сам';
    if (step.target) {
      requestAnimationFrame(() => {
        if (!state.tourActive || renderId !== state.tourRenderId) return;
        state.tourTarget = $(step.target);
        if (!state.tourTarget) return setTimeout(() => {
          if (state.tourActive && renderId === state.tourRenderId) renderTour();
        }, 120);
        if (step.requireAction === false) {
          mountTourCardNearTarget(state.tourTarget);
        } else {
          mountTourCardOverlay();
          state.tourTarget.classList.add('tour-active-target');
          state.tourTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });
          setTimeout(() => {
            if (state.tourActive && renderId === state.tourRenderId) positionTourTarget();
          }, 360);
        }
      });
    } else {
      mountTourCardInline();
    }
    analytics.send('onboarding_step', { step: state.tour + 1, role: state.role });
  }

  function advanceTour(praise = '') {
    if (state.tourAdvancing) return;
    state.tourAdvancing = true;
    state.tourAwaitingContinue = false;
    state.tourPraiseText = '';
    clearTourTarget();
    clearInlineTourPraise();
    $('.onboarding-card').hidden = true;
    $('#onboarding').hidden = false;
    setTimeout(() => {
      state.tour++;
      state.tourAdvancing = false;
      state.tour >= tourSteps().length ? finishTour(false) : renderTour();
    }, 120);
  }

  function showTourPraiseCard(praise) {
    mountTourCardInline();
    $('.onboarding-icon').textContent = '💛';
    $('#tourTitle').textContent = 'Молодець!';
    $('#tourText').textContent = praise || 'Усе вийшло. Переходь далі, коли будеш готовий.';
    $('#tourTapHint').hidden = true;
    const next = $('[data-action="next-tour"]');
    const skip = $('[data-action="skip-tour"]');
    next.hidden = false;
    next.textContent = 'Продовжити, коли готовий';
    skip.textContent = state.tourBranch ? 'Обрати інший розділ' : 'Вийти з навчання';
  }

  function clearInlineTourPraise() {
    $('#tourInlinePraise')?.remove();
  }

  function showInlineTourPraise(praise, completedTarget = null) {
    const sheet = completedTarget?.closest('.modal-sheet') || $('.modal.open .modal-sheet');
    const activeScreen = $('.screen.active');
    if (!sheet && !activeScreen) return false;
    clearInlineTourPraise();
    const box = document.createElement('div');
    box.id = 'tourInlinePraise';
    box.className = 'tour-inline-praise';
    const title = document.createElement('strong');
    title.textContent = '💛 Молодець!';
    const copy = document.createElement('p');
    copy.textContent = praise || 'Усе вийшло. Переходь далі, коли будеш готовий.';
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'primary';
    button.dataset.action = 'next-tour';
    button.textContent = 'Продовжити, коли готовий';
    if (state.tourBranch) {
      const other = document.createElement('button');
      other.type = 'button';
      other.className = 'ghost full';
      other.dataset.action = 'choose-another-section';
      other.textContent = 'Спробувати інший розділ';
      box.append(title, copy, other, button);
    } else box.append(title, copy, button);
    if (sheet) {
      if (completedTarget && sheet.contains(completedTarget)) completedTarget.insertAdjacentElement('afterend', box);
      else sheet.append(box);
    } else if (completedTarget && activeScreen.contains(completedTarget)) {
      completedTarget.insertAdjacentElement('afterend', box);
    } else {
      const anchor = activeScreen.querySelector('.feed-heading, .screen-title, .hero-copy');
      if (anchor) anchor.insertAdjacentElement('afterend', box);
      else activeScreen.prepend(box);
    }
    if (!sheet) requestAnimationFrame(() => box.scrollIntoView({ behavior: 'smooth', block: 'center' }));
    return true;
  }

  function completeTourAction(praise) {
    const completedTarget = state.tourTarget;
    clearTourTarget();
    state.tourAwaitingContinue = true;
    state.tourPraiseText = praise || '';
    $('#onboarding').hidden = true;
    setTimeout(() => {
      if (!showInlineTourPraise(state.tourPraiseText, completedTarget)) showTourPraiseCard(state.tourPraiseText);
    }, 30);
  }

  function handleGuidedClick(event) {
    if (!state.tourActive || state.tourAdvancing) return;
    const step = tourSteps()[state.tour];
    if (!step?.target || step.requireAction === false) return;
    const expected = $(step.target);
    if (expected && (event.target === expected || expected.contains(event.target))) {
      if (step.chooseNav) {
        const nav = event.target.closest('[data-nav]');
        if (!nav) return;
        state.tourBranch = nav.dataset.nav;
        const label = $('small', nav)?.textContent || 'цей розділ';
        return completeTourAction(`Гарний вибір — «${label}»! Відкриваю й покажу можливості саме тут.`);
      }
      completeTourAction(step.praise);
    }
  }

  function startTour() {
    closeModals();
    switchRole(state.role);
    state.tour = 0;
    state.tourBranch = '';
    state.tourActive = true;
    state.tourAdvancing = false;
    state.tourAwaitingContinue = false;
    state.tourPraiseText = '';
    $('#onboarding').hidden = false;
    renderTour();
  }

  function finishTour(skipped = false) {
    clearTourTarget();
    clearInlineTourPraise();
    closeModals();
    state.tourActive = false;
    state.tourAdvancing = false;
    state.tourAwaitingContinue = false;
    state.tourPraiseText = '';
    mountTourCardOverlay();
    $('#onboarding').hidden = true;
    localStorage.setItem('mriia_tour_seen', '1');
    if (!skipped) toast('Навчання завершено. Ти молодець! 🏆');
    analytics.send(skipped ? 'onboarding_skip' : 'onboarding_complete', { role: state.role });
  }

  function setupRatings() {
    $$('.rating-field').forEach(field => {
      const name = field.dataset.name;
      $('.rating', field).innerHTML = [1,2,3,4,5].map(n => `<button type="button" data-rating="${name}" data-value="${n}" aria-label="${n} з 5">${n}</button>`).join('');
    });
  }

  async function submitFeedback(form) {
    const formData = new FormData(form);
    const ratings = {};
    $$('[data-rating].selected').forEach(button => ratings[button.dataset.rating] = Number(button.dataset.value));
    if (Object.keys(ratings).length < 3) return toast('Постав, будь ласка, три оцінки');
    const payload = {
      session_id: state.sessionId,
      ratings,
      features: formData.getAll('features'),
      comment: String(formData.get('comment') || '').trim(),
      would_use: formData.get('would_use'),
      duration_seconds: Math.round((Date.now() - state.startedAt) / 1000),
      ts: new Date().toISOString()
    };
    let stored = CAN_USE_API ? 'server' : 'local';
    if (CAN_USE_API) try {
      const response = await fetch(`${API_BASE}/feedback.php`, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
      if (!response.ok) throw new Error('feedback unavailable');
    } catch {
      stored = 'local';
    }
    if (stored === 'local') {
      const saved = JSON.parse(localStorage.getItem('mriia_feedback') || '[]');
      saved.push(payload);
      localStorage.setItem('mriia_feedback', JSON.stringify(saved));
    }
    analytics.send('feedback_submit', { ratings, would_use: payload.would_use, stored });
    form.innerHTML = `<div class="success-state"><div class="big">💛</div><h3>Дякуємо за чесність</h3><p>${stored === 'server' ? 'Відгук збережено анонімно.' : 'Відгук збережено на цьому пристрої. Після підключення сервера форма надсилатиме його команді.'}</p><button class="primary" type="button" data-action="close-modal">Готово</button></div>`;
  }

  document.addEventListener('click', event => {
    handleGuidedClick(event);
    const welcomeRole = event.target.closest('[data-welcome-role]');
    if (welcomeRole) return chooseWelcomeRole(welcomeRole.dataset.welcomeRole);

    const careerTab = event.target.closest('[data-career-tab]');
    if (careerTab) {
      $$('[data-career-tab]').forEach(button => button.classList.toggle('active', button === careerTab));
      const content = careerTab.dataset.careerTab === 'consultations'
        ? '<article><strong>Кар’єрна консультація · 25 хв</strong><small>Резюме, пошук першої роботи та підготовка до співбесіди.</small></article><article><strong>Профорієнтація · 40 хв</strong><small>Знайдемо сильні сторони й можливі напрями розвитку.</small></article>'
        : careerTab.dataset.careerTab === 'trainings'
          ? '<article><strong>CV без канцеляризмів</strong><small>3 заняття · практика й портфоліо.</small></article><article><strong>Перша співбесіда</strong><small>Тренування запитань і чесний зворотний зв’язок.</small></article>'
          : '<article><strong>Молодший SMM-спеціаліст · 92% збігу</strong><small>Полтава · часткова зайнятість · навчання.</small></article><article><strong>Координатор/ка подій · 86% збігу</strong><small>Кременчук · гібридно · наставник.</small></article>';
      showDemoAction({ icon: '🚀', eyebrow: 'КАР’ЄРНИЙ ЦЕНТР', title: careerTab.textContent.trim(), body: content });
      return;
    }

    const centerTalentFilter = event.target.closest('[data-center-talent-filters] button');
    if (centerTalentFilter) {
      $$('[data-center-talent-filters] button').forEach(button => button.classList.toggle('active', button === centerTalentFilter));
      showDemoAction({ icon: '◎', eyebrow: 'ФІЛЬТР КАНДИДАТІВ', title: centerTalentFilter.textContent.trim(), body: `<article><strong>Знайдено потенційних кандидатів: ${centerTalentFilter.textContent.trim() === 'IT' ? '18' : '24'}</strong><small>Показані лише профілі, власники яких дозволили професійний пошук.</small></article>` });
      return;
    }

    const role = event.target.closest('button[data-role]');
    if (role) return switchRole(role.dataset.role);

    const nav = event.target.closest('[data-nav]');
    if (nav) return showScreen(nav.dataset.nav);

    const eventCard = event.target.closest('[data-event]');
    if (eventCard) return openEventCard(eventCard);

    const rating = event.target.closest('[data-rating]');
    if (rating) {
      $$(`[data-rating="${rating.dataset.rating}"]`).forEach(el => el.classList.toggle('selected', el === rating));
      return analytics.send('rating_select', { metric: rating.dataset.rating, value: Number(rating.dataset.value) });
    }

    const actionTarget = event.target.closest('[data-action]');
    const action = actionTarget?.dataset.action;
    if (!action) return;
    if (action === 'enter-demo') enterDemo(true);
    if (action === 'enter-free') enterDemo(false);
    if (action === 'open-registration' || action === 'edit-registration') { prepareRegistration(action === 'open-registration' ? state.welcomeRole : state.role); openModal('#registrationModal'); }
    if (action === 'go-home') showScreen('home');
    if (action === 'start-tour') startTour();
    if (action === 'go-explore') showScreen('explore');
    if (action === 'go-career') showScreen('career');
    if (action === 'go-my-plan') showScreen('my-plan');
    if (action === 'center-events') showScreen('center-events');
    if (action === 'center-services') showScreen('center-services');
    if (action === 'employer-talent') showScreen('employer-talent');
    if (action === 'notifications') showDemoAction({ icon: '♡', eyebrow: 'СПОВІЩЕННЯ', title: 'У тебе 4 оновлення', body: '<article><strong>Оля підтримала твою ідею</strong><small>12 хв тому</small></article><article><strong>Міра знайшла нове стажування</strong><small>Схожість із твоїми інтересами — 91%</small></article><article><strong>ОМЦ підтвердив участь</strong><small>Сертифікат уже в профілі</small></article>' });
    if (action === 'create-story') showDemoAction({ icon: '＋', eyebrow: 'НОВА ІСТОРІЯ', title: 'Поділися короткою історією', body: '<label><strong>Текст історії</strong><textarea id="demoStoryText" maxlength="180" placeholder="Що сталося або кого ти шукаєш?"></textarea></label><div class="choice-row"><button type="button" data-action="story-format">Фото</button><button type="button" data-action="story-format">Відео</button><button type="button" data-action="story-format">Текст</button></div><button class="primary" type="button" data-action="publish-story">Опублікувати історію</button>' });
    if (action === 'open-story') openStory(actionTarget.dataset.story);
    if (action === 'open-story-profile') openStoryProfile();
    if (action === 'open-explore-result') openExploreResult(actionTarget.dataset.resultId);
    if (action === 'open-team-details') openTeamOpportunity(actionTarget);
    if (action === 'story-cta') {
      const story = stories[state.currentStory] || stories.center;
      closeModals();
      if (story.destination === 'event') {
        state.selectedEvent = { id: 'lab', title: story.title, date: '22 серпня · Полтава', description: story.text };
        $('#eventModalTitle').textContent = story.title; $('#eventModalDate').textContent = '22 СЕРПНЯ · ПОЛТАВА'; $('#eventModalDescription').textContent = story.text; $('#eventModalDetails').innerHTML = '<span>🕒 17:00–20:00</span><span>📍 Молодіжний центр</span><span>♿ Простір доступний</span>'; openModal('#eventModal');
      } else showScreen(story.destination);
    }
    if (action === 'story-format') { const button = event.target.closest('[data-action="story-format"]'); $$('.choice-row button', button.closest('.demo-action-body')).forEach(item => item.classList.toggle('active', item === button)); }
    if (action === 'publish-story') { closeModals(); toast('Історію опубліковано у демострічці'); analytics.send('story_publish_demo'); }
    if (action === 'like-post') {
      const button = event.target.closest('[data-action="like-post"]');
      const count = $('span', button);
      button.classList.toggle('active');
      button.firstChild.textContent = button.classList.contains('active') ? '♥ ' : '♡ ';
      if (count) count.textContent = Number(count.textContent) + (button.classList.contains('active') ? 1 : -1);
      analytics.send('feed_like', { post_id: button.closest('[data-post-id]')?.dataset.postId });
      rememberInterest(button.closest('[data-post-id]')?.dataset.postId || 'корисні дописи');
    }
    if (action === 'comment-post') { openModal('#commentsModal'); analytics.send('feed_comments_open'); }
    if (action === 'share-post') shareDemo('Допис із молодіжної соцмережі');
    if (action === 'save-post') {
      const button = event.target.closest('[data-action="save-post"]');
      button.classList.toggle('active');
      button.textContent = button.classList.contains('active') ? '▰' : '▱';
      toast(button.classList.contains('active') ? 'Допис збережено' : 'Допис прибрано зі збережених');
      if (button.classList.contains('active')) rememberInterest(button.closest('[data-post-id]')?.dataset.postId || 'збережені дописи');
    }
    if (action === 'follow-source') {
      const button = event.target.closest('[data-action="follow-source"]');
      button.textContent = button.dataset.following === 'true' ? 'Підписатися' : 'Ви підписані';
      button.dataset.following = button.dataset.following === 'true' ? 'false' : 'true';
      toast(button.dataset.following === 'true' ? 'Підписку додано' : 'Підписку скасовано');
    }
    if (action === 'post-more') showDemoAction({ icon: '•••', eyebrow: 'МЕНЮ ДОПИСУ', title: 'Що зробити?', body: '<div class="choice-row"><button type="button" data-action="post-menu-choice">Скопіювати посилання</button><button type="button" data-action="post-menu-choice">Не рекомендувати</button><button type="button" data-action="post-menu-choice">Поскаржитися</button></div>' });
    if (action === 'post-menu-choice') { const label = event.target.textContent.trim(); closeModals(); toast(`${label}: дію виконано`); analytics.send('post_menu_demo', { choice: label }); }
    if (action === 'load-feed') {
      const extra = $$('[data-extra-feed]');
      state.feedExtrasLoaded = true;
      extra.forEach(post => post.hidden = false);
      event.target.closest('[data-action="load-feed"]').textContent = 'Рекомендації оновлено ✓';
      renderRoleFeed();
      toast(`Додано ${extra.length} нові історії`);
      analytics.send('feed_more_loaded', { count: extra.length });
    }
    if (action === 'open-feedback') { analytics.send('feedback_open'); openModal('#feedbackModal'); }
    if (action === 'open-ai') { analytics.send('ai_open'); updateMiraMemoryUI(); openModal('#aiModal'); }
    if (action === 'voice-mira') startMiraVoice();
    if (action === 'toggle-mira-sound') {
      state.miraSound = !state.miraSound;
      const button = event.target.closest('[data-action="toggle-mira-sound"]');
      button.classList.toggle('active', state.miraSound);
      button.setAttribute('aria-pressed', String(state.miraSound));
      button.textContent = state.miraSound ? '🔊' : '🔇';
      if (!state.miraSound) window.speechSynthesis?.cancel();
      toast(state.miraSound ? 'Міра озвучуватиме відповіді' : 'Озвучення вимкнено');
    }
    if (action === 'clear-mira-memory') {
      state.miraMemory = { name: state.userName, role: state.role, tone: 'тепло й коротко', interests: [], actions: 0 };
      localStorage.setItem('mriia_mira_memory', JSON.stringify(state.miraMemory));
      updateMiraMemoryUI();
      toast('Персональну пам’ять Міри очищено');
    }
    if (action === 'close-modal') {
      stopCamera();
      closeModals();
      clearInlineTourPraise();
      if (state.tourAwaitingContinue) setTimeout(() => showTourPraiseCard(state.tourPraiseText), 30);
    }
    if (action === 'register-event') { analytics.send('event_register_demo', { event_id: state.selectedEvent?.id || 'lab' }); closeModals(); toast('Готово! Місце заброньовано в деморежимі'); }
    if (action === 'save-event') saveSelectedEvent();
    if (action === 'open-saved-event') {
      const savedId = event.target.closest('[data-saved-id]')?.dataset.savedId;
      const saved = state.savedEvents.find(item => item.id === savedId);
      if (saved) {
        state.selectedEvent = saved;
        $('#eventModalDate').textContent = saved.date;
        $('#eventModalTitle').textContent = saved.title;
        $('#eventModalDescription').textContent = saved.description || 'Збережена можливість із твого персонального маршруту.';
        openModal('#eventModal');
      }
    }
    if (action === 'add-calendar') { analytics.send('calendar_add_demo', { event_id: state.selectedEvent?.id }); toast('Додано до демокалендаря'); }
    if (action === 'confirm-team-interest') {
      const button = state.pendingTeamInterestButton;
      if (button) { button.textContent = 'Запит надіслано ✓'; button.disabled = true; }
      state.pendingTeamInterestButton = null;
      closeModals();
      analytics.send('team_interest_demo');
      toast('Команда отримає запит на знайомство');
    }
    if (action === 'wave') { const button = event.target.closest('[data-action="wave"]'); button.textContent = '✓'; button.disabled = true; analytics.send('wave_demo'); toast('Привітання надіслано 👋'); }
    if (action === 'open-certificate') showDemoAction({ icon: '✓', eyebrow: 'ПІДТВЕРДЖЕНИЙ РЕЗУЛЬТАТ', title: 'Фасилітація молодіжних подій', body: '<article><strong>Видано Полтавським ОМЦ · 18 серпня</strong><small>12 навчальних годин · практична фасилітація · сертифікат перевірено організатором.</small></article><article><strong>Навички</strong><small>Комунікація · робота з групою · планування зустрічі</small></article>' });
    if (action === 'show-all-achievements') showDemoAction({ icon: '🏆', eyebrow: 'ПОРТФОЛІО', title: 'Підтверджені результати', body: '<article><strong>Фасилітація молодіжних подій</strong><small>Сертифікат · 12 годин</small></article><article><strong>Медіаволонтерство</strong><small>36 підтверджених годин</small></article><article><strong>Лабораторія власних проєктів</strong><small>Учасник/ця · проєктний пітч</small></article>' });
    if (action === 'open-people-filters') showDemoAction({ icon: '⌕', eyebrow: 'ПОШУК ЛЮДЕЙ', title: 'Кого хочеш знайти?', body: '<div class="choice-row"><button type="button" data-action="apply-demo-filter">Поруч</button><button type="button" data-action="apply-demo-filter">Дизайн</button><button type="button" data-action="apply-demo-filter">Волонтерство</button><button type="button" data-action="apply-demo-filter">Шукають команду</button></div><article><strong>Безпечний пошук</strong><small>Показуємо тільки дозволені дані та спільні інтереси.</small></article>' });
    if (action === 'apply-demo-filter') { const filter = event.target.textContent.trim(); closeModals(); toast(`Фільтр «${filter}» застосовано`); }
    if (action === 'show-all-organizations') showDemoAction({ icon: '▦', eyebrow: 'СПІЛЬНОТИ', title: 'Організації та простори', body: '<article><strong>Полтавський ОМЦ</strong><small>Події · консультації · безпечний простір</small></article><article><strong>GreenHub</strong><small>Екологія · волонтерство</small></article><article><strong>ГО «Нова Полтава»</strong><small>Проєкти · громади</small></article><article><strong>Простір «Точка»</strong><small>Культура · спільноти</small></article><article><strong>Фонд молодіжних ініціатив</strong><small>Мікрогранти · менторство</small></article>' });
    if (action === 'open-organization') { const card = event.target.closest('[data-action="open-organization"]'); showDemoAction({ icon: card.querySelector('span')?.textContent || '▦', eyebrow: 'ПРОФІЛЬ ОРГАНІЗАЦІЇ', title: card.querySelector('strong')?.textContent || 'Організація', body: `<article><strong>${card.querySelector('small')?.textContent || 'Можливості для молоді'}</strong><small>Перевірений демопрофіль · події, дописи, контакти й відгуки.</small></article><button class="primary" type="button" data-action="follow-organization">Підписатися</button>` }); }
    if (action === 'follow-organization') { event.target.textContent = 'Ви підписані ✓'; event.target.disabled = true; toast('Організацію додано у підписки'); }
    if (action === 'connect-mentor') showDemoAction({ icon: 'АК', eyebrow: 'МЕНТОРСТВО', title: 'Анна Кравець', body: '<article><strong>Фасилітація · комунікація · молодіжні проєкти</strong><small>3 підтверджені навички · 48 годин менторства.</small></article><button class="primary" type="button" data-action="send-mentor-request">Надіслати запит на знайомство</button>' });
    if (action === 'send-mentor-request') { closeModals(); toast('Анна отримає запит на знайомство'); }
    if (action === 'open-microgrant') showDemoAction({ icon: '₴', eyebrow: 'МІКРОГРАНТ', title: '«Дій поруч»', body: '<article><strong>До 30 000 грн · дедлайн 12 вересня</strong><small>Ініціативи для громади, культури, волонтерства та добробуту.</small></article><article><strong>Проста заявка</strong><small>Проблема · ідея · команда · план · бюджет. Міра допоможе пройти кожен крок.</small></article>' });
    if (action === 'open-checklist') showDemoAction({ icon: '♿', eyebrow: 'ДОСТУПНИЙ ЗАХІД', title: 'Чекліст «Без порогів»', body: '<article><strong>До події</strong><small>Доступний маршрут · супровід · інформація простою мовою.</small></article><article><strong>Під час події</strong><small>Тиха зона · субтитри · перерви · контакт відповідальної людини.</small></article>' });
    if (action === 'create-event') { analytics.send('center_create_event_open'); openModal('#createEventModal'); }
    if (action === 'open-scanner') { analytics.send('qr_scanner_open'); $('#scanResult').hidden = true; openModal('#scannerModal'); }
    if (action === 'start-camera') startCamera();
    if (action === 'simulate-scan') { $('#scanResult').hidden = false; analytics.send('qr_checkin_demo', { participant: 'demo-user' }); toast('Учасника відмічено'); }
    if (action === 'share-event') shareDemo('Лабораторія власних проєктів');
    if (action === 'share-center') shareDemo('Полтавський обласний молодіжний центр');
    if (action === 'center-analytics') { analytics.send('center_analytics_open'); openModal('#analyticsModal'); }
    if (action === 'add-service') showDemoAction({ icon: '＋', eyebrow: 'НОВА ПОСЛУГА', title: 'Додати консультацію', body: '<label><strong>Назва</strong><input id="demoServiceName" value="Кар’єрна консультація"></label><label><strong>Формат</strong><select><option>Онлайн</option><option>Офлайн</option><option>Гібридний</option></select></label><button class="primary" type="button" data-action="save-demo-service">Додати до розкладу</button>' });
    if (action === 'save-demo-service') { closeModals(); toast('Послугу додано до розкладу'); analytics.send('service_created_demo'); }
    if (action === 'service-checkin') { const button = event.target.closest('[data-action="service-checkin"]'); button.textContent = button.textContent.includes('Посилання') ? 'Скопійовано ✓' : 'Завершити'; button.classList.add('active'); toast('Статус консультації оновлено'); }
    if (action === 'open-calendar') showDemoAction({ icon: '▦', eyebrow: 'КАЛЕНДАР ЦЕНТРУ', title: 'Розклад на сьогодні', body: '<article><strong>10:30 · Кар’єрна консультація</strong><small>Онлайн · Аліна Сова</small></article><article><strong>14:00 · Психологічна підтримка</strong><small>Кабінет 3 · анонімний запис</small></article><article><strong>17:30 · Юридична консультація</strong><small>Онлайн · права волонтерів</small></article>' });
    if (action === 'edit-event-draft') { $('#createEventForm').elements.title.value = 'Майстерня волонтерських команд'; openModal('#createEventModal'); toast('Чернетку завантажено у форму'); }
    if (action === 'invite-talent') { const button = event.target.closest('[data-action="invite-talent"]'); button.textContent = 'Запрошено ✓'; button.disabled = true; analytics.send('talent_invite_demo'); toast('Запрошення надіслано потенційному кандидату'); }
    if (action === 'register-organization') { analytics.send('organization_register_open'); openModal('#organizationModal'); }
    if (action === 'create-vacancy') { analytics.send('vacancy_create_open'); openModal('#vacancyModal'); }
    if (action === 'preview-vacancy') renderVacancyPreview();
    if (action === 'view-applicants') { analytics.send('applicants_open_demo'); showScreen('employer-talent'); toast('Показано кандидатів із найкращим збігом'); }
    if (action === 'share-employer') shareDemo('Профіль роботодавця «Вектор»');
    if (action === 'career-apply') { const button = event.target.closest('[data-action="career-apply"]'); button.textContent = 'Відгук надіслано ✓'; button.disabled = true; analytics.send('career_apply_demo'); toast('Роботодавець побачить профіль і підтверджені навички'); }
    if (action === 'book-consultation' || action === 'show-all-consultations') showDemoAction({ icon: '◷', eyebrow: 'КАР’ЄРНА КОНСУЛЬТАЦІЯ', title: 'Обери зручний час', body: '<div class="choice-row"><button type="button" data-action="book-time">Сьогодні 18:00</button><button type="button" data-action="book-time">Завтра 12:30</button><button type="button" data-action="book-time">П’ятниця 16:00</button></div><article><strong>Аліна Сова · 25 хв</strong><small>Резюме, перша робота, профорієнтація або підготовка до співбесіди.</small></article>' });
    if (action === 'book-time') { const time = event.target.textContent.trim(); closeModals(); toast(`Консультацію заброньовано: ${time}`); analytics.send('career_consultation_demo', { time }); }
    if (action === 'join-training') { const button = event.target.closest('[data-action="join-training"]'); button.textContent = 'Ти в групі ✓'; button.disabled = true; analytics.send('career_training_demo'); toast('Місце на тренінгу заброньовано'); }
    if (action === 'crisis-call-112') showCrisisAction('call_112', '112 — єдина екстрена допомога', 'У робочій версії ця кнопка відкриє підтвердження дзвінка. Оператор визначить, яку службу направити.');
    if (action === 'crisis-call-102') showCrisisAction('call_102', '102 — поліція', 'У робочій версії ця кнопка відкриє підтвердження дзвінка до поліції.');
    if (action === 'crisis-call-1547') showCrisisAction('call_1547', '1547 — допомога у випадку насильства', 'У робочій версії ця кнопка відкриє підтвердження дзвінка на цілодобову безкоштовну лінію.');
    if (action === 'prepare-safety-message') {
      $('#crisisResponse').classList.add('is-ready');
      $('#crisisResponse').innerHTML = '<strong>Скопіюй і доповни лише те, що безпечно:</strong><p class="crisis-message-template">Мене звати ____. Я зараз у/біля ____. Мені або людині поруч загрожує ____. Потрібна допомога. Моя безпечна локація/орієнтир: ____.</p><small>Демо не копіює і не надсилає цей текст автоматично.</small>';
      speakMira('Я підготувала короткий шаблон. Доповни лише те, що безпечно повідомити.');
    }
    if (action === 'crisis-alert-team') showCrisisAction('alert_team', 'Черговий фахівець платформи', 'У робочій версії користувач підтвердить надсилання мінімального сигналу навченому фахівцю. У демо нічого не надсилається.');
    if (action === 'next-tour') advanceTour();
    if (action === 'choose-another-section') returnToTourMenu();
    if (action === 'skip-tour') {
      if (state.tourActive && state.tourBranch) returnToTourMenu();
      finishTour(true);
    }
  });

  $$('.modal').forEach(modal => {
    modal.inert = !modal.classList.contains('open');
    modal.addEventListener('click', event => { if (event.target === modal) { stopCamera(); closeModals(); } });
  });
  $$('[data-feed-mode]').forEach(button => button.addEventListener('click', () => {
    state.feedMode = button.dataset.feedMode;
    renderRoleFeed();
    analytics.send('feed_mode_change', { mode: state.feedMode, role: state.role });
  }));
  $$('.chip').forEach(chip => chip.addEventListener('click', () => {
    $$('.chip').forEach(el => el.classList.remove('active'));
    chip.classList.add('active');
    $$('.event-card').forEach(card => card.classList.toggle('hidden-by-filter', chip.dataset.filter !== 'all' && !card.dataset.tags.includes(chip.dataset.filter)));
    analytics.send('feed_filter', { filter: chip.dataset.filter });
  }));
  $$('.interest-grid button').forEach(button => button.addEventListener('click', () => { $('#searchInput').value = button.dataset.query; renderExplore(button.dataset.query); analytics.send('interest_select', { query: button.dataset.query }); }));
  $('#searchInput').addEventListener('change', event => rememberInterest(event.target.value));
  $('#searchInput').addEventListener('input', event => renderExplore(event.target.value));
  $$('.prompt-list button').forEach(button => button.addEventListener('click', () => {
    const responses = {
      team: '<strong>Є 3 варіанти поруч:</strong><br>• Екофестиваль шукає дизайнера<br>• Команда медіаволонтерів<br>• Відкрита зустріч авторів проєктів',
      volunteer: '<strong>Підібрала 2 короткі місії:</strong><br>• Зняти відео для ініціативи<br>• Допомогти на молодіжному фестивалі',
      help: '<strong>Можна звернутися без імені:</strong><br>• Чат із психологом<br>• Онлайн-консультація<br>• Перевірені кризові контакти'
      ,urgent: ''
      ,memory: `<strong>Ось що я пам’ятаю зараз:</strong><br>• звертатися: ${state.userName || 'ім’я ще не вказано'}<br>• роль: ${roleLabel(state.role)}<br>• інтереси: ${state.miraMemory.interests.length ? state.miraMemory.interests.join(', ') : 'ще знайомимося'}<br><small>Чутливі психологічні розмови не додаються до демопам’яті автоматично.</small>`
    };
    if (button.dataset.prompt === 'urgent') {
      openCrisisFlow('concern');
      return;
    }
    $('#aiResponse').innerHTML = responses[button.dataset.prompt];
    $('#aiResponse').hidden = false;
    speakMira(responses[button.dataset.prompt]);
    analytics.send('ai_prompt_demo', { prompt: button.dataset.prompt });
    if (button.dataset.prompt !== 'help' && button.dataset.prompt !== 'memory') rememberInterest(button.dataset.prompt);
  }));
  $('#welcomeNameForm').addEventListener('submit', event => {
    event.preventDefault();
    state.userName = $('#welcomeName').value.trim();
    if (!state.userName) return;
    updatePersonalization();
    saveMiraMemory({ name: state.userName });
    showWelcomeStep('role');
    analytics.send('welcome_name_entered');
  });
  $('#registrationRole').addEventListener('change', event => updateRegistrationRequirements(event.target.value));
  $('#registrationForm').addEventListener('submit', event => {
    event.preventDefault();
    const form = event.currentTarget;
    const data = new FormData(form);
    const role = String(data.get('profile_role') || 'youth');
    updateRegistrationRequirements(role);
    if (!form.reportValidity()) return;
    state.userName = String(data.get('display_name') || '').trim();
    state.role = role;
    state.welcomeRole = role;
    state.employment = String(data.get('employment') || '');
    state.position = String(data.get('position') || '').trim();
    state.affiliation = String(data.get('affiliation') || '').trim();
    state.profileVisible = data.get('profile_visibility') === 'on';
    localStorage.setItem('mriia_demo_profile', JSON.stringify(profilePayload()));
    updatePersonalization();
    saveMiraMemory({ name: state.userName, role });
    closeModals();
    $('#welcomeFlow').hidden = true;
    switchRole(role);
    showScreen(role === 'center' ? 'center-profile' : role === 'employer' ? 'employer-profile' : 'profile');
    toast('Демопрофіль створено — дані вже відображаються');
    analytics.send('demo_registration_complete', { role, employment: state.employment || 'not_set', has_affiliation: Boolean(state.affiliation) });
  });
  $('#feedComposerForm').addEventListener('submit', event => {
    event.preventDefault();
    const field = event.currentTarget.elements.post;
    const article = document.createElement('article');
    article.className = 'social-post priority-post';
    article.dataset.feedKind = 'following';
    article.dataset.feedRole = state.role;
    article.dataset.postId = `demo-${Date.now()}`;
    article.innerHTML = `<header><span class="post-avatar person-story"></span><div><strong></strong><small>Щойно · демопублікація</small></div><button type="button" data-action="post-more">•••</button></header><p></p><div class="post-actions"><button type="button" data-action="like-post">♡ <span>0</span></button><button type="button" data-action="comment-post">○ <span>0</span></button><button type="button" data-action="share-post">↗</button><button type="button" class="save" data-action="save-post">▱</button></div>`;
    const publisher = state.role === 'center' ? { name: 'Полтавський ОМЦ', mark: 'ОМЦ' } : state.role === 'employer' ? { name: 'Агенція «Вектор»', mark: 'V' } : { name: state.userName || 'Демо-користувач', mark: (state.userName || 'Д').slice(0, 1).toUpperCase() };
    $('.post-avatar', article).textContent = publisher.mark;
    $('header strong', article).textContent = publisher.name;
    $('p', article).textContent = field.value.trim();
    $('#socialFeed').prepend(article);
    event.currentTarget.reset();
    toast('Публікацію додано у демострічку');
    analytics.send('feed_post_demo', { length: $('p', article).textContent.length, role: state.role });
  });
  $('#commentForm').addEventListener('submit', event => {
    event.preventDefault();
    const field = event.currentTarget.elements.comment;
    const article = document.createElement('article');
    article.innerHTML = '<span class="avatar a1"></span><div><strong></strong><p></p><small>Щойно · Відповісти</small></div>';
    $('.avatar', article).textContent = (state.userName || 'Д').slice(0, 1).toUpperCase();
    $('strong', article).textContent = state.userName || 'Демо-користувач';
    $('p', article).textContent = field.value.trim();
    $('#commentList').append(article);
    event.currentTarget.reset();
    toast('Коментар додано в деморежимі');
    analytics.send('feed_comment_demo');
  });
  $('#miraForm').addEventListener('submit', event => {
    event.preventDefault();
    const question = $('#miraQuestion').value.trim();
    if (!question) return;
    const crisis = assessCrisis(question);
    if (crisis) {
      $('#aiResponse').innerHTML = `<strong>${state.userName || 'Друже'}, я чую, що це може бути небезпечно.</strong><br>Я відкрию короткі перевірені варіанти допомоги. Ти сам/сама обереш наступну дію.`;
      $('#aiResponse').hidden = false;
      event.currentTarget.reset();
      setTimeout(() => openCrisisFlow(crisis), 350);
      return;
    }
    const reply = miraReply(question);
    $('#aiResponse').innerHTML = reply;
    $('#aiResponse').hidden = false;
    speakMira(reply);
    analytics.send('mira_question_demo', { length: question.length, role: state.role });
    if (!/псих|важко|допомог|самот|тривог|депрес/i.test(question)) rememberInterest(question.split(/\s+/).slice(0, 4).join(' '));
    event.currentTarget.reset();
  });
  $('#feedbackForm').addEventListener('submit', event => { event.preventDefault(); submitFeedback(event.currentTarget); });
  $('#createEventForm').addEventListener('submit', event => {
    event.preventDefault();
    const data = new FormData(event.currentTarget);
    const title = String(data.get('title'));
    $('#centerEventList').insertAdjacentHTML('afterbegin', `<article><span class="status planned">НОВИЙ</span><div><h4>${title.replace(/[<>]/g, '')}</h4><p>0 реєстрацій · щойно опубліковано</p></div><button data-action="share-event">↗</button></article>`);
    analytics.send('center_event_created_demo', { title, format: data.get('format') });
    closeModals();
    showScreen('center-events');
    toast('Демозахід опубліковано');
  });
  $('#organizationForm').addEventListener('submit', event => {
    event.preventDefault();
    const data = new FormData(event.currentTarget);
    analytics.send('organization_registered_demo', { offer: data.get('offer') });
    closeModals();
    toast('Демопрофіль організації створено');
  });
  $('#vacancyForm').addEventListener('submit', event => {
    event.preventDefault();
    const data = new FormData(event.currentTarget);
    const title = String(data.get('title') || 'Нова вакансія').replace(/[<>]/g, '');
    state.lastVacancy = {
      title,
      category: String(data.get('category') || ''),
      city: String(data.get('city') || ''),
      format: String(data.get('format') || ''),
      skills: String(data.get('skills') || '').split(/[,;]+/).map(skill => skill.trim()).filter(Boolean)
    };
    renderCandidates('', 'recommended');
    const matches = candidatePool.filter(candidate => candidateScore(candidate) >= 68).length;
    $('#vacancyList').insertAdjacentHTML('afterbegin', `<article><span class="status live">НОВА</span><div><h4>${title}</h4><p>0 відгуків · ${matches} рекомендованих</p></div><button data-action="view-applicants">›</button></article>`);
    analytics.send('vacancy_created_demo', { title, type: data.get('type'), format: data.get('format'), category: data.get('category'), city: data.get('city'), matches });
    closeModals();
    showScreen('employer-vacancies');
    toast(`Вакансію опубліковано · Міра знайшла ${matches} кандидатів`);
  });
  $('#talentSearch').addEventListener('input', event => renderCandidates(event.target.value, state.candidateFilter));
  $$('[data-candidate-filter]').forEach(button => button.addEventListener('click', () => {
    state.candidateFilter = button.dataset.candidateFilter;
    $$('[data-candidate-filter]').forEach(item => item.classList.toggle('active', item === button));
    renderCandidates($('#talentSearch').value, state.candidateFilter);
    analytics.send('candidate_filter', { filter: state.candidateFilter });
  }));
  $('#vacancyForm').addEventListener('input', updateVacancyQuality);
  $('#vacancyForm').addEventListener('change', updateVacancyQuality);

  document.addEventListener('input', event => {
    if (event.target.matches('input[type="text"],input[type="search"],input:not([type]),textarea')) moderateField(event.target);
  });
  document.addEventListener('submit', event => {
    const fields = $$('input[type="text"],input[type="search"],input:not([type]),textarea', event.target);
    const invalid = fields.filter(field => !moderateField(field));
    if (!invalid.length) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    invalid[0].focus();
    invalid[0].reportValidity();
    toast('Відредагуйте позначений текст — публікацію зупинено');
    analytics.send('content_moderation_block', { form: event.target.id || 'unknown', fields: invalid.length });
  }, true);

  window.addEventListener('resize', () => { positionTourTarget(); positionFloatingUi(); });
  $('.app-shell').addEventListener('scroll', positionTourTarget, { passive: true });

  setupRatings();
  renderExplore();
  renderCandidates();
  updateVacancyQuality();
  updatePersonalization();
  updateMiraMemoryUI();
  $('#welcomeName').value = state.userName;
  showWelcomeStep('name');
  switchRole(state.role);
  positionFloatingUi();
  analytics.send('session_start', { referrer: document.referrer || 'direct' });
})();
