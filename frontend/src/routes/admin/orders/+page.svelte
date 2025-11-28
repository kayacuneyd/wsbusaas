<script lang="ts">
  import { onMount } from 'svelte';
  import { API_URL } from '$lib/api';
  import { adminAuth } from '$lib/stores/auth';
  import { getOrderStatusBadgeClasses, getOrderStatusLabel } from '$lib/constants/orderStatus';

  let orders: any[] = [];
  let loading = true;

  onMount(async () => {
    try {
      const res = await fetch(`${API_URL}/admin/orders`, {
        headers: {
          'Authorization': `Bearer ${$adminAuth.token}`
        }
      });
      const data = await res.json();
      if (data.success) {
        orders = data.orders;
      }
    } catch (e) {
      console.error(e);
    } finally {
      loading = false;
    }
  });

  function resolveStatus(order: any) {
    return order?.order_status ?? order?.status;
  }
</script>

<svelte:head>
  <title>Siparişler - Admin - Bezmidar Sitebuilder</title>
</svelte:head>

<div class="flex justify-between items-center mb-6">
  <h1 class="text-2xl font-bold text-gray-900">Siparişler</h1>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-md">
  <ul class="divide-y divide-gray-200">
    {#if loading}
      <li class="px-4 py-4 sm:px-6 text-center">Yükleniyor...</li>
    {:else if orders.length === 0}
      <li class="px-4 py-4 sm:px-6 text-center text-gray-500">Henüz sipariş yok.</li>
    {:else}
      {#each orders as order}
        <li>
          <a href="/admin/orders/{order.order_id}" class="block hover:bg-gray-50">
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
                    {order.customer_email}
                  </p>
                </div>
                <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                  <p>
                    Sipariş No: {order.order_id}
                  </p>
                </div>
              </div>
            </div>
          </a>
        </li>
      {/each}
    {/if}
  </ul>
</div>
