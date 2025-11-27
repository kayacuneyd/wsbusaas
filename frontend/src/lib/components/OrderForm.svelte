<script lang="ts">
  import { createOrder } from '$lib/api';
  import { goto } from '$app/navigation';
  import { customerAuth } from '$lib/stores/auth';

  export let domain = '';
  export let packageType = 'starter';

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

      // Pass token to createOrder (we need to update api.ts or handle it here)
      // Let's handle fetch here directly or update api.ts. 
      // Updating api.ts is cleaner but for speed let's do it here or assume api.ts can take headers.
      // Actually, let's update createOrder in api.ts to accept token.
      
      // Temporary: Direct fetch to support auth header
      const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';
      const res = await fetch(`${API_URL}/orders`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${$customerAuth.token}`
        },
        body: JSON.stringify(orderData)
      });
      
      const data = await res.json();

      if (data.success) {
        // Open payment in new tab
        window.open(data.payment_url, '_blank');
        
        // Redirect to order status page in current tab
        goto(`/order-status/${data.order_id}`);
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
    <label for="email" class="block text-sm font-medium text-gray-700">Email Adresi</label>
    <input 
      type="email" 
      id="email" 
      bind:value={customerEmail} 
      required 
      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-3 border"
    />
  </div>

  <div>
    <label for="name" class="block text-sm font-medium text-gray-700">Ad Soyad</label>
    <input 
      type="text" 
      id="name" 
      bind:value={customerName} 
      required 
      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-3 border"
    />
  </div>

  {#if error}
    <div class="text-red-600 text-sm">{error}</div>
  {/if}

  <button 
    type="submit" 
    disabled={loading}
    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
  >
    {loading ? 'İşleniyor...' : 'Ödemeye Geç (299€)'}
  </button>
</form>
