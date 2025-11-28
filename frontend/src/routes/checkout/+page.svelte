<script lang="ts">
  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import { customerAuth } from '$lib/stores/auth';
  import { cart } from '$lib/stores/cart';
  import OrderForm from '$lib/components/OrderForm.svelte';
  import { onMount } from 'svelte';

  let domain = '';
  let packageType = 'starter';

  onMount(() => {
    // 1. Check URL params first
    const urlDomain = $page.url.searchParams.get('domain');
    const urlPackage = $page.url.searchParams.get('package');

    if (urlDomain) {
      domain = urlDomain;
      packageType = urlPackage || 'starter';
      
      // Update cart
      cart.set({ domain, packageType, price: 0 }); // Price logic can be added later
    } else if ($cart) {
      // 2. Fallback to Cart
      domain = $cart.domain;
      packageType = $cart.packageType;
    }

    // 3. Auth Check
    if (!$customerAuth.isAuthenticated) {
      // Redirect to login, preserving checkout intent
      goto(`/login?redirect=/checkout`);
    }
  });
</script>

<svelte:head>
  <title>Ödeme - Bezmidar Sitebuilder</title>
</svelte:head>

<div class="py-12 bg-gray-50 min-h-screen">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
      <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Sipariş Özeti</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">Lütfen bilgilerinizi kontrol edin.</p>
      </div>
      <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
        <dl class="sm:divide-y sm:divide-gray-200">
          <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">Paket</dt>
            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">Starter Paket</dd>
          </div>
          <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">Domain</dt>
            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-bold text-blue-600">
              {domain || 'Seçilmedi'}
            </dd>
          </div>
          <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">Fiyat</dt>
            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-bold">299€ / yıl</dd>
          </div>
        </dl>
      </div>
    </div>

    <div class="mt-8 bg-white shadow sm:rounded-lg p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-6">Müşteri Bilgileri</h3>
      <OrderForm {domain} {packageType} />
    </div>
  </div>
</div>
