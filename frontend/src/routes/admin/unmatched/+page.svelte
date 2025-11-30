<script lang="ts">
  import { onMount } from 'svelte';
  import { API_URL } from '$lib/api';
  import { adminAuth } from '$lib/stores/auth';

  let payments: any[] = [];
  let pendingOrders: any[] = [];
  let loading = true;
  let processing = false;

  async function loadData() {
    loading = true;
    try {
      // Load Unmatched Payments
      const resPayments = await fetch(`${API_URL}/admin/unmatched.php`, {
        headers: { 'Authorization': `Bearer ${$adminAuth.token}` }
      });
      const dataPayments = await resPayments.json();
      if (dataPayments.success) {
        payments = dataPayments.payments;
      }

      // Load Pending Orders (for assignment dropdown)
      const resOrders = await fetch(`${API_URL}/admin/orders.php`, {
        headers: { 'Authorization': `Bearer ${$adminAuth.token}` }
      });
      const dataOrders = await resOrders.json();
      if (dataOrders.success) {
        pendingOrders = dataOrders.orders.filter((o: any) => o.payment_status === 'pending');
      }
    } catch (e) {
      console.error(e);
    } finally {
      loading = false;
    }
  }

  async function assignOrder(paymentId: number, orderId: string) {
    if (!orderId) return alert('Lütfen bir sipariş seçin.');
    if (!confirm('Bu ödemeyi seçilen siparişle eşleştirmek istediğinize emin misiniz? Kurulum otomatik başlayacak.')) return;

    processing = true;
    try {
      const res = await fetch(`${API_URL}/admin/unmatched.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${$adminAuth.token}`
        },
        body: JSON.stringify({ action: 'assign', payment_id: paymentId, order_id: orderId })
      });
      const data = await res.json();
      if (data.success) {
        alert('Eşleştirme başarılı! Kurulum kuyruğa alındı.');
        loadData(); // Refresh
      } else {
        alert(data.error || 'Hata oluştu.');
      }
    } catch (e) {
      alert('İşlem başarısız.');
    } finally {
      processing = false;
    }
  }

  async function ignorePayment(paymentId: number) {
    if (!confirm('Bu ödemeyi listeden kaldırmak istediğinize emin misiniz?')) return;
    
    processing = true;
    try {
      const res = await fetch(`${API_URL}/admin/unmatched.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${$adminAuth.token}`
        },
        body: JSON.stringify({ action: 'ignore', payment_id: paymentId })
      });
      const data = await res.json();
      if (data.success) {
        loadData();
      }
    } catch (e) {
      console.error(e);
    } finally {
      processing = false;
    }
  }

  onMount(loadData);
</script>

<svelte:head>
  <title>Sahipsiz Ödemeler - Admin</title>
</svelte:head>

<div class="mb-6">
  <h1 class="text-2xl font-semibold text-gray-900">Sahipsiz Ödemeler</h1>
  <p class="mt-1 text-sm text-gray-500">
    Ruul'dan gelen ancak isim/email uyuşmazlığı nedeniyle otomatik eşleşemeyen ödemeler burada listelenir.
  </p>
</div>

{#if loading}
  <div>Yükleniyor...</div>
{:else if payments.length === 0}
  <div class="bg-white shadow rounded-lg p-6 text-center text-gray-500">
    Şu an bekleyen sahipsiz ödeme yok. Harika! 🎉
  </div>
{:else}
  <div class="bg-white shadow overflow-hidden sm:rounded-md">
    <ul class="divide-y divide-gray-200">
      {#each payments as payment}
        <li class="px-4 py-4 sm:px-6">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-medium text-gray-900">
                {payment.customer_name || 'İsimsiz'} 
                <span class="text-sm text-gray-500">({payment.email})</span>
              </h3>
              <p class="text-sm text-gray-500">
                Gelen Veri: {payment.raw_payload}
              </p>
              <p class="text-xs text-gray-400 mt-1">
                {new Date(payment.created_at).toLocaleString()}
              </p>
            </div>
            
            <div class="flex items-center space-x-4">
              <select 
                class="block w-64 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                on:change={(e) => assignOrder(payment.id, e.currentTarget.value)}
              >
                <option value="">Bir Sipariş Seçin...</option>
                {#each pendingOrders as order}
                  <option value={order.order_id}>
                    #{order.order_id} - {order.customer_name} ({order.domain_name})
                  </option>
                {/each}
              </select>

              <button 
                on:click={() => ignorePayment(payment.id)}
                class="text-red-600 hover:text-red-900 text-sm"
              >
                Yoksay
              </button>
            </div>
          </div>
        </li>
      {/each}
    </ul>
  </div>
{/if}
