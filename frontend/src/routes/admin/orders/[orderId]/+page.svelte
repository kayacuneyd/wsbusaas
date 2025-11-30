<script lang="ts">
  import { page } from '$app/stores';
  import { onMount } from 'svelte';
  import { API_URL } from '$lib/api';
  import { adminAuth } from '$lib/stores/auth';
  import {
    ORDER_STATES,
    getOrderStatusBadgeClasses,
    getOrderStatusLabel
  } from '$lib/constants/orderStatus';

  let orderId = $page.params.orderId;
  let order: any = null;
  let logs: any[] = [];
  let loading = true;
  let updating = false;
  let selectedStatus = ORDER_STATES[0].key;
  let note = '';

  $: statusHistory = Array.isArray(order?.status_history) ? order.status_history : [];
  $: sortedHistory = [...statusHistory].sort((a, b) => {
    const aDate = a?.created_at ? new Date(a.created_at).getTime() : 0;
    const bDate = b?.created_at ? new Date(b.created_at).getTime() : 0;
    return bDate - aDate;
  });

  async function loadOrder() {
    try {
      const res = await fetch(`${API_URL}/admin/orders/${orderId}`, {
        headers: { 'Authorization': `Bearer ${$adminAuth.token}` }
      });
      const data = await res.json();
      if (data.success) {
        order = data.order;
        logs = data.logs || [];
        selectedStatus = order?.order_status ?? ORDER_STATES[0].key;
      }
    } catch (e) {
      console.error(e);
    } finally {
      loading = false;
    }
  }

  async function submitStatusUpdate() {
    if (!selectedStatus) return;
    if (!confirm(`Durumu \"${getOrderStatusLabel(selectedStatus)}\" olarak değiştirmek istediğinize emin misiniz?`)) return;

    updating = true;
    try {
      const res = await fetch(`${API_URL}/admin/orders/${orderId}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${$adminAuth.token}`
        },
        body: JSON.stringify({ status: selectedStatus, note: note || null })
      });
      const data = await res.json();
      if (data.success) {
        order = data.order;
        logs = data.logs || [];
        note = '';
      } else {
        alert(data.error || 'Durum güncellenemedi.');
      }
    } catch (e) {
      alert('Güncelleme başarısız.');
    } finally {
      updating = false;
    }
  }

  async function markPaymentReceived() {
    if (!confirm('Ödeme alındı olarak işaretlemek istediğinize emin misiniz? Bu işlem sipariş durumunu "Ödeme Alındı" yapacaktır.')) return;

    updating = true;
    try {
      const res = await fetch(`${API_URL}/admin/orders/${orderId}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${$adminAuth.token}`
        },
        body: JSON.stringify({ status: 'payment_received', note: 'Ödeme manuel olarak onaylandı.' })
      });
      const data = await res.json();
      if (data.success) {
        order = data.order;
        logs = data.logs || [];
        selectedStatus = 'payment_received';
        alert('Ödeme başarıyla onaylandı!');
      } else {
        alert(data.error || 'Ödeme onaylanamadı.');
      }
    } catch (e) {
      alert('İşlem başarısız.');
    } finally {
      updating = false;
    }
  }

  async function startDeployment() {
    if (!confirm('Website kurulum sürecini manuel olarak başlatmak istediğinize emin misiniz?')) return;

    updating = true;
    try {
      const res = await fetch(`${API_URL}/admin/deploy.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${$adminAuth.token}`
        },
        body: JSON.stringify({ order_id: orderId })
      });
      const data = await res.json();
      if (data.success) {
        alert('Kurulum süreci başarıyla başlatıldı! İşlem geçmişini takip edebilirsiniz.');
        loadOrder(); // Reload to see new logs
      } else {
        alert(data.error || 'Kurulum başlatılamadı.');
      }
    } catch (e) {
      alert('İşlem başarısız.');
    } finally {
      updating = false;
    }
  }

  function formatDate(value?: string) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleString('tr-TR');
  }

  function resolveStatus(order: any) {
    return order?.order_status ?? order?.status;
  }

  onMount(loadOrder);
</script>

<svelte:head>
  <title>Sipariş Detayı - Admin - Bezmidar Sitebuilder</title>
</svelte:head>

<div class="mb-6">
  <a href="/admin/orders" class="text-blue-600 hover:text-blue-800">← Listeye Dön</a>
</div>

{#if loading}
  <div class="text-center">Yükleniyor...</div>
{:else if order}
  <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
      <div>
        <h3 class="text-lg leading-6 font-medium text-gray-900">Sipariş Detayı</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">{order.order_id}</p>
      </div>
      <div>
        <span class={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getOrderStatusBadgeClasses(resolveStatus(order))}`}>
          {getOrderStatusLabel(resolveStatus(order))}
        </span>
      </div>
    </div>
    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
      <dl class="sm:divide-y sm:divide-gray-200">
        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
          <dt class="text-sm font-medium text-gray-500">Domain</dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{order.domain_name}</dd>
        </div>
        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
          <dt class="text-sm font-medium text-gray-500">Müşteri</dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            {order.customer_name}<br>
            <a href="mailto:{order.customer_email}" class="text-blue-600">{order.customer_email}</a>
          </dd>
        </div>
        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
          <dt class="text-sm font-medium text-gray-500">Paket</dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{order.package_type}</dd>
        </div>
        {#if (order.order_status ?? order.status) === 'pending_confirmation'}
        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-amber-50 border-l-4 border-amber-400">
          <dt class="text-sm font-medium text-amber-800">Hızlı İşlem</dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <button
              class="inline-flex items-center gap-2 rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
              on:click={markPaymentReceived}
              disabled={updating}
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {updating ? 'İşleniyor...' : 'Ödeme Alındı'}
            </button>
            <p class="mt-2 text-xs text-amber-700">Ruul.io ödeme e-postasını aldıysanız, bu butona tıklayarak ödemeyi onaylayabilirsiniz.</p>
          </dd>
        </div>
        {/if}

        {#if (order.order_status ?? order.status) === 'payment_received' || (order.order_status ?? order.status) === 'pending_deployment'}
        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-blue-50 border-l-4 border-blue-400">
          <dt class="text-sm font-medium text-blue-800">Kurulum İşlemleri</dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <button
              class="inline-flex items-center gap-2 rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
              on:click={startDeployment}
              disabled={updating}
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {updating ? 'Başlatılıyor...' : 'Kurulumu Başlat'}
            </button>
            <p class="mt-2 text-xs text-blue-700">Otomatik kurulum başlamadıysa veya hata aldıysa, bu buton ile süreci manuel olarak tetikleyebilirsiniz.</p>
          </dd>
        </div>
        {/if}
        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
          <dt class="text-sm font-medium text-gray-500">Durum Güncelle</dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 space-y-3">
            <select
              bind:value={selectedStatus}
              disabled={updating}
              class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            >
              {#each ORDER_STATES as status}
                <option value={status.key}>{status.label}</option>
              {/each}
            </select>
            <textarea
              bind:value={note}
              rows="3"
              placeholder="Opsiyonel not. Müşteri panelinde durum mesajı olarak gösterilir."
              class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            ></textarea>
            <div class="flex items-center justify-between">
              <p class="text-xs text-gray-500">Not, müşteriye profesyonel ve açıklayıcı bir mesaj olarak gösterilir.</p>
              <button
                class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                on:click={submitStatusUpdate}
                disabled={updating}
              >
                {updating ? 'Güncelleniyor...' : 'Durumu Güncelle'}
              </button>
            </div>
          </dd>
        </div>
      </dl>
    </div>
  </div>

  <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
    <div class="px-4 py-5 sm:px-6">
      <h3 class="text-lg leading-6 font-medium text-gray-900">Durum Geçmişi</h3>
      <p class="mt-1 text-sm text-gray-500">Ruul.io e-postaları doğrulandıktan sonra manuel olarak güncelleyin.</p>
    </div>
    <div class="border-t border-gray-200">
      {#if sortedHistory.length > 0}
        <ul class="divide-y divide-gray-200">
          {#each sortedHistory as history}
            <li class="px-4 py-4 sm:px-6 text-sm">
              <div class="flex items-center justify-between">
                <p class="font-semibold text-gray-900">{getOrderStatusLabel(history.status)}</p>
                <p class="text-xs text-gray-500">{formatDate(history.created_at)}</p>
              </div>
              <p class="text-xs text-gray-500">Güncelleyen: {history.changed_by ?? 'sistem'}</p>
              {#if history.note}
                <p class="mt-2 text-gray-700">{history.note}</p>
              {/if}
            </li>
          {/each}
        </ul>
      {:else}
        <p class="px-4 py-4 text-sm text-gray-500">Henüz kayıt yok.</p>
      {/if}
    </div>
  </div>

  <div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
      <h3 class="text-lg leading-6 font-medium text-gray-900">İşlem Geçmişi (Logs)</h3>
    </div>
    <div class="border-t border-gray-200">
      <ul class="divide-y divide-gray-200">
        {#each logs as log}
          <li class="px-4 py-4 sm:px-6">
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium text-gray-900">{log.message}</p>
              <p class="text-sm text-gray-500">{new Date(log.created_at).toLocaleString()}</p>
            </div>
            <p class="text-sm text-gray-500 mt-1">{log.log_type}</p>
          </li>
        {/each}
      </ul>
    </div>
  </div>
{/if}
