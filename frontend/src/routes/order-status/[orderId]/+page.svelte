<script lang="ts">
  import { page } from '$app/stores';
  import { onMount } from 'svelte';
  import { API_URL } from '$lib/api';
  import OrderStatus from '$lib/components/OrderStatus.svelte';

  let orderId = $page.params.orderId;
  let order: any = null;
  let loading = true;
  let error = '';

  onMount(async () => {
    try {
      const res = await fetch(`${API_URL}/orders/${orderId}`);
      const data = await res.json();
      if (data.success) {
        order = data.order;
      } else {
        error = data.message || 'Sipariş bulunamadı.';
      }
    } catch (e) {
      error = 'Bağlantı hatası.';
    } finally {
      loading = false;
    }
  });
</script>

<svelte:head>
  <title>Sipariş Takip - WebsiteBuilder</title>
</svelte:head>

<div class="py-12 bg-gray-50 min-h-screen">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    {#if loading}
      <div class="text-center py-12">
        <div class="spinner">Yükleniyor...</div>
      </div>
    {:else if error}
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Hata!</strong>
        <span class="block sm:inline">{error}</span>
      </div>
    {:else if order}
      <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-900">Sipariş Takip</h1>
        <p class="text-gray-600 mt-2">Sipariş No: {order.order_id}</p>
        <p class="text-gray-600">Domain: {order.domain_name}</p>
      </div>
      
      <OrderStatus {order} />
    {/if}
  </div>
</div>
