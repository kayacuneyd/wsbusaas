```html
<script lang="ts">
  import { page } from '$app/stores';
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { orderStore } from '$lib/stores/order';
  import { customerAuth } from '$lib/stores/auth';
  import OrderForm from '$lib/components/OrderForm.svelte';

  let domain = $page.url.searchParams.get('domain') || '';
  let packageType = $page.url.searchParams.get('package') || 'starter';

  onMount(() => {
    if (!$customerAuth.isAuthenticated) {
      goto(`/login?redirect=${encodeURIComponent($page.url.pathname + $page.url.search)}`);
    }
    if (domain) {
      $orderStore.domain = domain;
    }
  });
</script>

<svelte:head>
  <title>Ödeme - WebsiteBuilder</title>
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
              {$orderStore.domain || 'Seçilmedi'}
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
      <OrderForm />
    </div>
  </div>
</div>
