<script lang="ts">
  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import { customerAuth } from '$lib/stores/auth';
  import { cart } from '$lib/stores/cart';
  import OrderForm from '$lib/components/OrderForm.svelte';
  import { onMount } from 'svelte';
  import { API_URL } from '$lib/api';

  let domain = '';
  let packageType = 'starter';
  let selectedPackage: any = null;
  let loadingPackage = true;

  onMount(async () => {
    // 1. Check URL params first
    const urlDomain = $page.url.searchParams.get('domain');
    const urlPackage = $page.url.searchParams.get('package');

    if (urlDomain) {
      domain = urlDomain;
      packageType = urlPackage || 'starter';
      
      // Update cart
      cart.set({ domain, packageType, price: 0 }); 
    } else if ($cart) {
      // 2. Fallback to Cart
      domain = $cart.domain;
      packageType = $cart.packageType;
    }

    // 3. Auth Check
    if (!$customerAuth.isAuthenticated) {
      goto(`/login?redirect=/checkout`);
      return;
    }

    // 4. Fetch Package Details
    try {
      const res = await fetch(`${API_URL}/packages.php`);
      const data = await res.json();
      if (data.success) {
        selectedPackage = data.packages.find((p: any) => p.slug === packageType) || data.packages[0];
      }
    } catch (e) {
      console.error('Failed to load package', e);
    } finally {
      loadingPackage = false;
    }
  });
</script>

<svelte:head>
  <title>Ödeme - Bezmidar Sitebuilder</title>
</svelte:head>

<div class="py-12 bg-gray-50 min-h-screen">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- WARNING ALERT -->
    <div class="rounded-md bg-yellow-50 p-4 mb-6 border-l-4 border-yellow-400">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-yellow-800">ÖNEMLİ UYARI</h3>
          <div class="mt-2 text-sm text-yellow-700">
            <p>
              Ödeme sayfasında gireceğiniz <strong>İsim Soyisim</strong> ve <strong>E-posta</strong> adresi, 
              burada kayıt olurken kullandığınız bilgilerle <strong>BİREBİR AYNI OLMALIDIR.</strong>
            </p>
            <p class="mt-1">
              Aksi takdirde sistem ödemenizi otomatik olarak eşleştiremeyebilir ve kurulum gecikebilir.
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
      <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Sipariş Özeti</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">Lütfen bilgilerinizi kontrol edin.</p>
      </div>
      <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
        <dl class="sm:divide-y sm:divide-gray-200">
          <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">Paket</dt>
            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
              {selectedPackage ? selectedPackage.name : 'Yükleniyor...'}
            </dd>
          </div>
          <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">Domain</dt>
            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-bold text-blue-600">
              {domain || 'Seçilmedi'}
            </dd>
          </div>
          <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">Fiyat</dt>
            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-bold">
              {#if selectedPackage}
                {selectedPackage.price} {selectedPackage.currency ?? 'EUR'} / yıl
              {:else}
                ...
              {/if}
            </dd>
          </div>
        </dl>
      </div>
    </div>

    <div class="mt-8 bg-white shadow sm:rounded-lg p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-6">Müşteri Bilgileri</h3>
      <OrderForm 
        {domain} 
        {packageType} 
        price={selectedPackage?.price} 
        currency={selectedPackage?.currency} 
      />
    </div>
  </div>
</div>
