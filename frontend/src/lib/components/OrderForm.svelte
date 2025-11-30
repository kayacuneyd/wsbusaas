<script lang="ts">
  import { createOrder } from '$lib/api';
  import { goto } from '$app/navigation';
  import { customerAuth } from '$lib/stores/auth';
  import { cart } from '$lib/stores/cart';

  export let price: number | undefined = undefined;
  export let currency: string | undefined = 'EUR';

  let customerName = $customerAuth.user?.full_name || '';
  let customerEmail = $customerAuth.user?.email || '';
  let loading = false;
  let error = '';

  async function handleSubmit() {
    loading = true;
    error = '';

    try {
      const orderData = {
        domain_name: domain,
        package_type: packageType,
        customer_name: customerName,
        customer_email: customerEmail
      };

      const data = await createOrder(orderData, $customerAuth.token);

      if (data.success && data.payment?.url && data.order?.order_id) {
        // Clear cart
        cart.set(null);

        // Open payment in new tab
        window.open(data.payment.url, '_blank');
        
        // Redirect to order status page in current tab
        goto(`/order-status/${data.order.order_id}`);
      } else {
        error = data.error || 'Sipariş oluşturulamadı.';
      }
    } catch (e) {
      error = 'Bir hata oluştu.';
    } finally {
      loading = false;
    }
  }
</script>

<form on:submit|preventDefault={handleSubmit} class="space-y-6">
  <div>
    <label class="block text-sm font-medium text-gray-700">
      Email Adresi
      <input 
        type="email" 
        bind:value={customerEmail} 
        required 
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-3 border"
      />
    </label>
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">
      Ad Soyad
      <input 
        type="text" 
        bind:value={customerName} 
        required 
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-3 border"
      />
    </label>
  </div>

  {#if error}
    <div class="text-red-600 text-sm">{error}</div>
  {/if}

  <button 
    type="submit" 
    disabled={loading}
    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
  >
    {loading ? 'İşleniyor...' : `Ödemeye Geç (${price ?? '...'} ${currency ?? 'EUR'})`}
  </button>
</form>
