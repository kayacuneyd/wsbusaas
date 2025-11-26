<script lang="ts">
  import { Api } from '$lib/api';
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  let order_id = Number(localStorage.getItem('orderId') || 0);
  let status = 'Bekleniyor...';

  const poll = async () => {
    const res = await Api.orderStatus(order_id);
    if (res.status === 'live') {
      goto('/success');
    } else if (res.status === 'failed') {
      goto('/failed');
    }
  };

  onMount(() => {
    const interval = setInterval(poll, 4000);
    return () => clearInterval(interval);
  });
</script>

<main class="center">
  <p>{status}</p>
</main>

<style>
  .center { display:flex; align-items:center; justify-content:center; min-height:60vh; font-size:1.1rem; }
</style>
