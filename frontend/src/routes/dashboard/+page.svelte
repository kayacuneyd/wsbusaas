<script lang="ts">
  import { onMount } from 'svelte';
  import { customerAuth } from '$lib/stores/auth';
  import { goto } from '$app/navigation';
  import { API_URL } from '$lib/api';
  import {
    getOrderProgressPercent,
    getOrderState,
    getOrderStatusBadgeClasses,
    getOrderStatusLabel
  } from '$lib/constants/orderStatus';

  let orders: any[] = [];
  let loading = true;
  let error = '';
  let latestOrder: any = null;

  onMount(async () => {
    if (!$customerAuth.isAuthenticated) {
      goto('/login');
      return;
    }

    try {
      const res = await fetch(`${API_URL}/user/orders`, {
        headers: {
          'Authorization': `Bearer ${$customerAuth.token}`
        }
      });
      const data = await res.json();
      
      if (data.success) {
        orders = data.orders;
        latestOrder = orders[0] ?? null;
      } else {
        error = data.error || 'Siparişler yüklenemedi.';
      }
    } catch (e) {
      error = 'Bağlantı hatası.';
    } finally {
      loading = false;
    }
  });

  $: latestOrderState = latestOrder ? getOrderState(latestOrder.order_status ?? latestOrder.status) : null;
  $: latestOrderProgress = latestOrder ? getOrderProgressPercent(latestOrder.order_status ?? latestOrder.status) : 0;

  function formatDate(value?: string) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString('tr-TR');
  }

  function resolveStatus(order: any) {
    return order?.order_status ?? order?.status;
  }
</script>

<svelte:head>
  <title>Hesabım - Bezmidar Sitebuilder</title>
</svelte:head>

<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
  <div class="md:flex md:items-center md:justify-between mb-8">
    <div class="flex-1 min-w-0">
      <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
        Hesabım
      </h2>
      <p class="mt-1 text-sm text-gray-500">
        Hoşgeldin, {$customerAuth.user?.full_name}
      </p>
    </div>
    <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
      <button 
        on:click={() => {
          customerAuth.set({ isAuthenticated: false, token: null, user: null });
          window.location.href = '/';
        }}
        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
      >
        Çıkış Yap
      </button>
      <a href="/" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        Yeni Sipariş Oluştur
      </a>
    </div>
  </div>

  {#if loading}
    <div class="text-center py-12">Yükleniyor...</div>
  {:else if error}
    <div class="bg-red-50 p-4 rounded-md">
      <div class="flex">
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Hata</h3>
          <div class="mt-2 text-sm text-red-700">
            <p>{error}</p>
          </div>
        </div>
      </div>
    </div>
  {:else if orders.length === 0}
    <div class="text-center py-12 bg-white rounded-lg shadow">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">Siparişiniz yok</h3>
      <p class="mt-1 text-sm text-gray-500">Henüz bir website paketi satın almadınız.</p>
      <div class="mt-6">
        <a href="/" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
          Paketleri İncele
        </a>
      </div>
    </div>
  {:else}
    {#if latestOrder && latestOrderState}
      <div class="mb-10 rounded-xl bg-white p-6 shadow">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Son İşlem</p>
            <h3 class="text-2xl font-bold text-gray-900">{latestOrderState.label}</h3>
            <p class="text-gray-700">{latestOrderState.messages.tr}</p>
            <p class="text-xs italic text-gray-500">{latestOrderState.messages.en}</p>
            <p class="mt-2 text-sm text-gray-500">
              {latestOrder.status_message ?? latestOrderState.messages.tr}
            </p>
          </div>
          <div class="text-sm text-gray-500">
            <p>Güncelleme: {formatDate(latestOrder.status_updated_at ?? latestOrder.updated_at)}</p>
            <a
              class="mt-2 inline-flex items-center justify-center rounded-md border border-blue-200 px-4 py-2 font-semibold text-blue-700 transition hover:bg-blue-600 hover:text-white"
              href={`/order-status/${latestOrder.order_id}`}
            >
              Detayları Gör
            </a>
          </div>
        </div>
        <div class="mt-6">
          <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
            <div
              class="h-full rounded-full bg-blue-600 transition-all duration-500"
              style={`width: ${latestOrderProgress}%`}
            ></div>
          </div>
          <p class="mt-2 text-xs text-gray-500">
            Ruul.io işleminiz tamamlandıktan sonra durum manuel olarak onaylanır.
          </p>
        </div>
      </div>
    {/if}

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <ul class="divide-y divide-gray-200">
        {#each orders as order}
          <li>
            <a href={`/order-status/${order.order_id}`} class="block hover:bg-gray-50">
              <div class="px-4 py-4 sm:px-6">
                <div class="flex items-center justify-between">
                  <p class="text-sm font-medium text-blue-600 truncate">
                    {order.domain_name}
                  </p>
                  <div class="ml-2 flex-shrink-0 flex">
                    <p class={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getOrderStatusBadgeClasses(resolveStatus(order))}`}>
                      {getOrderStatusLabel(resolveStatus(order))}
                    </p>
                  </div>
                </div>
                <div class="mt-2 sm:flex sm:justify-between">
                  <div class="sm:flex">
                    <p class="flex items-center text-sm text-gray-500">
                      Paket: {order.package_type}
                    </p>
                  </div>
                  <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                    <p>
                      Sipariş Tarihi: {new Date(order.created_at).toLocaleDateString('tr-TR')}
                    </p>
                  </div>
                </div>
              </div>
            </a>
          </li>
        {/each}
      </ul>
    </div>
  {/if}
</div>
