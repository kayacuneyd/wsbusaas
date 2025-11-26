<script lang="ts">
  import { Api } from '$lib/api';
  import { goto } from '$app/navigation';
  let order_id = Number(localStorage.getItem('orderId') || 0);
  let payment_link = '';
  let error = '';

  const pay = async () => {
    try {
      const res = await Api.initiatePayment(order_id);
      payment_link = res.payment_link;
      window.location.href = payment_link;
    } catch (e: any) {
      error = e.message;
    }
  };
</script>

<section class="panel">
  <h2>Checkout</h2>
  <p>Sipariş: {order_id}</p>
  <button on:click={pay}>Ödemeye git</button>
  {#if error}<p class="error">{error}</p>{/if}
</section>

<style>
  .panel { max-width: 420px; margin: 80px auto; padding: 24px; border:1px solid #e5e7eb; border-radius: 12px; text-align:center; }
  button { padding: 12px 16px; background:#16a34a; border:none; color:#fff; border-radius:8px; }
  .error { color:#dc2626; margin-top:10px; }
</style>
