<script lang="ts">
  import { page } from '$app/stores';
  import { onMount } from 'svelte';
  import { API_URL } from '$lib/api';
  import { adminAuth } from '$lib/stores/auth';

  let orderId = $page.params.orderId;
  let order: any = null;
  let logs: any[] = [];
  let loading = true;
  let updating = false;

  const statuses = [
    'created',
    'payment_received',
    'domain_purchased',
    'hosting_setup',
    'template_deployed',
    'completed',
    'failed'
  ];

  async function loadOrder() {
    try {
      const res = await fetch(`${API_URL}/admin/orders/${orderId}`, {
        headers: { 'Authorization': `Bearer ${$adminAuth.token}` }
      });
      const data = await res.json();
      if (data.success) {
        order = data.order;
        logs = data.logs || [];
      }
    } catch (e) {
      console.error(e);
    } finally {
      loading = false;
    }
  }

  async function updateStatus(newStatus: string) {
    if (!confirm(`Durumu "${newStatus}" olarak değiştirmek istediğinize emin misiniz?`)) return;
    
    updating = true;
    try {
      const res = await fetch(`${API_URL}/admin/orders/${orderId}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${$adminAuth.token}`
        },
        body: JSON.stringify({ status: newStatus })
      });
      const data = await res.json();
      if (data.success) {
        order = data.order;
        // Reload logs
        loadOrder();
      }
    } catch (e) {
      alert('Güncelleme başarısız.');
    } finally {
      updating = false;
    }
  }

  onMount(loadOrder);
</script>

<svelte:head>
  <title>Sipariş Detayı - Admin</title>
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
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
          {order.status === 'completed' ? 'bg-green-100 text-green-800' : 
           order.status === 'created' ? 'bg-gray-100 text-gray-800' : 
           'bg-yellow-100 text-yellow-800'}">
          {order.status}
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
        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
          <dt class="text-sm font-medium text-gray-500">Durum Güncelle</dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <div class="flex gap-2">
              <select 
                value={order.status} 
                on:change={(e) => updateStatus(e.currentTarget.value)}
                disabled={updating}
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md border"
              >
                {#each statuses as status}
                  <option value={status}>{status}</option>
                {/each}
              </select>
            </div>
          </dd>
        </div>
      </dl>
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
