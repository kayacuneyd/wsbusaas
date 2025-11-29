<script lang="ts">
  import { goto } from '$app/navigation';
  import { onMount } from 'svelte';
  import { fetchPackages } from '$lib/api';
  import { t } from 'svelte-i18n';

  type PackageItem = {
    id: number;
    name: string;
    slug: string;
    description?: string | null;
    price?: number | null;
    category?: string | null;
    payment_link?: string | null;
  };

  function scrollToPricing() {
    const el = document.getElementById('pricing');
    el?.scrollIntoView({ behavior: 'smooth' });
  }

  let packages: PackageItem[] = [];
  let loadingPackages = true;
  let packagesError = '';

  onMount(async () => {
    try {
      const data = await fetchPackages();
      if (data.success) {
        packages = data.packages || [];
      } else {
        packagesError = data.error || $t('home.pricing.error');
      }
    } catch (e) {
      packagesError = $t('home.pricing.error');
    } finally {
      loadingPackages = false;
    }
  });

  function formatPrice(price?: number | null) {
    if (price === null || price === undefined) return '—';
    return `${price}€`;
  }
</script>

<svelte:head>
  <title>Bezmidar Sitebuilder - {$t('home.hero_title')}</title>
  <meta name="description" content={$t('home.hero_subtitle')} />
</svelte:head>

<!-- Hero Section -->
<section class="bg-brand-dark text-white py-20 md:py-32 relative overflow-hidden">
  <!-- Background Pattern -->
  <div class="absolute inset-0 opacity-10">
    <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
      <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white" />
    </svg>
  </div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
    <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight mb-6 leading-tight">
      {$t('home.hero_title')}
    </h1>
    <p class="text-xl md:text-2xl text-brand-light max-w-3xl mx-auto mb-10 font-light">
      {$t('home.hero_subtitle')}
    </p>
    <div class="flex justify-center space-x-4">
      <a href="/domain-check" class="bg-brand-light text-brand-dark px-8 py-4 rounded-lg font-bold text-lg shadow-lg hover:bg-white transition duration-300 transform hover:-translate-y-1">
        {$t('home.cta_button')}
      </a>
    </div>
  </div>
</section>

<!-- Features Section -->
<section class="py-16 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
      <div class="p-6 bg-brand-bg rounded-xl hover:shadow-md transition-shadow">
        <div class="text-4xl mb-4">🌐</div>
        <h3 class="text-xl font-bold mb-2 text-brand-dark">{$t('home.features.domain_title')}</h3>
        <p class="text-gray-600">{$t('home.features.domain_desc')}</p>
      </div>
      <div class="p-6 bg-brand-bg rounded-xl hover:shadow-md transition-shadow">
        <div class="text-4xl mb-4">🖥️</div>
        <h3 class="text-xl font-bold mb-2 text-brand-dark">{$t('home.features.hosting_title')}</h3>
        <p class="text-gray-600">{$t('home.features.hosting_desc')}</p>
      </div>
      <div class="p-6 bg-brand-bg rounded-xl hover:shadow-md transition-shadow">
        <div class="text-4xl mb-4">🎨</div>
        <h3 class="text-xl font-bold mb-2 text-brand-dark">{$t('home.features.design_title')}</h3>
        <p class="text-gray-600">{$t('home.features.design_desc')}</p>
      </div>
      <div class="p-6 bg-brand-bg rounded-xl hover:shadow-md transition-shadow">
        <div class="text-4xl mb-4">🔧</div>
        <h3 class="text-xl font-bold mb-2 text-brand-dark">{$t('home.features.maintenance_title')}</h3>
        <p class="text-gray-600">{$t('home.features.maintenance_desc')}</p>
      </div>
    </div>
  </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="py-16 bg-brand-bg">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold text-brand-dark">{$t('home.pricing.title')}</h2>
      <p class="mt-4 text-xl text-gray-600">{$t('home.pricing.subtitle')}</p>
    </div>

    <div class="max-w-5xl mx-auto">
      {#if loadingPackages}
        <div class="text-center text-gray-500 py-12">
          <div class="spinner mb-4"></div>
          {$t('home.pricing.loading')}
        </div>
      {:else if packagesError}
        <div class="text-center text-red-600 py-12">{packagesError}</div>
      {:else if packages.length === 0}
        <div class="text-center text-gray-500 py-12">{$t('home.pricing.no_packages')}</div>
      {:else}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          {#each packages as pkg}
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200 flex flex-col hover:shadow-2xl transition-shadow duration-300 transform hover:-translate-y-1">
              <div class="px-6 py-6 bg-brand-dark text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-brand-light opacity-20 rounded-full blur-xl"></div>
                <h3 class="text-2xl font-bold relative z-10">{pkg.name}</h3>
                <p class="mt-1 text-sm text-brand-light capitalize relative z-10">{pkg.category || 'paket'}</p>
                <div class="mt-3 flex items-baseline gap-2 relative z-10">
                  <span class="text-4xl font-extrabold">{formatPrice(pkg.price)}</span>
                  <span class="text-sm text-brand-light">{$t('home.pricing.per_year')}</span>
                </div>
              </div>
              <div class="px-6 py-6 flex-1 flex flex-col gap-4">
                <p class="text-sm text-gray-700 min-h-[48px]">{pkg.description || 'Her şey dahil, kuruluma hazır paket.'}</p>
                <div class="mt-auto">
                  <a
                    href={`/domain-check?package=${encodeURIComponent(pkg.slug)}`}
                    class="block w-full bg-brand-dark text-white text-center px-6 py-3 rounded-lg font-bold hover:bg-opacity-90 transition duration-300"
                  >
                    {$t('home.pricing.select_button')}
                  </a>
                </div>
              </div>
            </div>
          {/each}
        </div>
      {/if}
    </div>
  </div>
</section>

<!-- How it Works -->
<section class="py-16 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold text-brand-dark">{$t('home.how_it_works.title')}</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center relative">
      <!-- Connecting Line (Desktop only) -->
      <div class="hidden md:block absolute top-24 left-1/6 right-1/6 h-0.5 bg-gray-200 -z-10"></div>

      <div class="relative bg-white p-4">
        <div class="w-16 h-16 bg-brand-light text-brand-dark rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4 shadow-sm border-4 border-white">1</div>
        <h3 class="text-xl font-bold mb-2 text-brand-dark">{$t('home.how_it_works.step1_title')}</h3>
        <p class="text-gray-600">{$t('home.how_it_works.step1_desc')}</p>
      </div>
      <div class="relative bg-white p-4">
        <div class="w-16 h-16 bg-brand-light text-brand-dark rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4 shadow-sm border-4 border-white">2</div>
        <h3 class="text-xl font-bold mb-2 text-brand-dark">{$t('home.how_it_works.step2_title')}</h3>
        <p class="text-gray-600">{$t('home.how_it_works.step2_desc')}</p>
      </div>
      <div class="relative bg-white p-4">
        <div class="w-16 h-16 bg-brand-light text-brand-dark rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4 shadow-sm border-4 border-white">3</div>
        <h3 class="text-xl font-bold mb-2 text-brand-dark">{$t('home.how_it_works.step3_title')}</h3>
        <p class="text-gray-600">{$t('home.how_it_works.step3_desc')}</p>
      </div>
    </div>
  </div>
</section>
