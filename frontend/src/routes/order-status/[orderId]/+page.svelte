<script lang="ts">
  import { page } from '$app/stores';
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { API_URL } from '$lib/api';
  import OrderStatus from '$lib/components/OrderStatus.svelte';
  import { customerAuth } from '$lib/stores/auth';

  let orderId = $page.params.orderId;
  let order: any = null;
  let loading = true;
  let error = '';
  let paymentUrl = '';

  onMount(async () => {
    const token = $customerAuth.token;

    if (!token) {
      goto(`/login?redirect=/order-status/${orderId}`);
      return;
    }

    try {
      const res = await fetch(`${API_URL}/orders/${orderId}`, {
        headers: {
          Authorization: `Bearer ${token}`
        }
      });

      if (res.status === 401) {
        goto(`/login?redirect=/order-status/${orderId}`);
        return;
      }

      const data = await res.json();
      if (data.success) {
        order = data.order;
        paymentUrl = data.payment?.url || data.order?.payment_link || '';
      } else {
        error = data.error || data.message || 'Sipariş bulunamadı.';
      }
    } catch (e) {
      error = 'Bağlantı hatası.';
    } finally {
      loading = false;
    }
  });

  $: isPending = (order?.order_status ?? order?.status) === 'pending_confirmation';

  function openPayment() {
    if (paymentUrl) {
      window.open(paymentUrl, '_blank', 'noopener');
    }
  }
</script>

<svelte:head>
  <title>Sipariş Takip - Bezmidar Sitebuilder</title>
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
        <div class="mt-4 flex gap-2">
          <button
            class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
            on:click={() => goto('/dashboard')}
          >
            Hesabıma Dön
          </button>
          <button
            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
            on:click={() => goto('/login?redirect=' + encodeURIComponent(`/order-status/${orderId}`))}
          >
            Giriş Yap
          </button>
        </div>
      </div>
    {:else if order}
      <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-900">Sipariş Takip</h1>
        <p class="text-gray-600 mt-2">Sipariş No: {order.order_id}</p>
        <p class="text-gray-600">Domain: {order.domain_name}</p>
      </div>

      {#if isPending && paymentUrl}
        <div class="mb-6 rounded-xl border-2 border-amber-300 bg-amber-50 p-5 shadow-md animate-pulse">
          <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="text-amber-800">
              <p class="text-lg font-bold">Ödeme Bekleniyor</p>
              <p class="text-sm">
                Ödemenizi tamamladığınızda siparişiniz otomatik olarak işleme alınacaktır. Ödeme sayfasını yeniden açmak için aşağıdaki butonu kullanın.
              </p>
            </div>
            <div class="flex flex-wrap gap-3">
              <button
                class="inline-flex items-center rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-700"
                on:click={openPayment}
              >
                Ödemeyi Tamamla
              </button>
              <button
                class="inline-flex items-center rounded-md border border-amber-300 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100"
                on:click={() => goto('/dashboard')}
              >
                Tüm Siparişlerim
              </button>
            </div>
          </div>
        </div>
      {/if}

      <!-- Payment Warning Animation -->
      {#if (order.order_status ?? order.status) === 'pending_confirmation'}
        <div class="mb-6 animate-pulse">
          <div class="rounded-lg border-2 border-amber-400 bg-gradient-to-r from-amber-50 to-yellow-50 p-6 shadow-lg">
            <div class="flex items-center justify-center gap-3">
              <svg class="w-8 h-8 text-amber-600 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              <div class="text-center">
                <p class="text-xl font-bold text-amber-800 animate-pulse">Ödeme İşleminizi Tamamlayınız</p>
                <p class="text-sm text-amber-700 mt-1">Ödeme onaylandıktan sonra siparişiniz işleme alınacaktır. Ödeme sayfasını kapattıysanız yeniden açabilirsiniz.</p>
              </div>
            </div>
          </div>
        </div>
      {/if}
      
      <OrderStatus {order} />
    {/if}
  </div>
</div>
