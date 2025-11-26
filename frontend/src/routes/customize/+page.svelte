<script lang="ts">
  import { Api } from '$lib/api';
  import { goto } from '$app/navigation';
  let primary_color = '#3b82f6';
  let secondary_color = '#f97316';
  let font = 'Inter';
  let logo_base64 = '';
  let order_id = Number(localStorage.getItem('orderId') || 0);
  let domain = localStorage.getItem('domain') || '';
  const theme_id = localStorage.getItem('selectedTheme') || 'theme1';

  const handleFile = async (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    const buf = await file.arrayBuffer();
    const b64 = btoa(String.fromCharCode(...new Uint8Array(buf)));
    logo_base64 = `data:${file.type};base64,${b64}`;
  };

  const submit = async () => {
    if (!order_id) {
      const created = await Api.createOrder({
        email: 'customer@example.com',
        theme_id,
        amount: 99,
        domain,
        primary_color,
        secondary_color,
        font
      });
      order_id = created.order_id;
      localStorage.setItem('orderId', String(order_id));
      localStorage.setItem('domain', domain);
    }
    await Api.updateTheme({ order_id, primary_color, secondary_color, font, logo_base64 });
    goto('/checkout');
  };
</script>

<main class="layout">
  <label>Domain <input bind:value={domain} placeholder="example.com"></label>
  <label>Primary Color <input type="color" bind:value={primary_color}></label>
  <label>Secondary Color <input type="color" bind:value={secondary_color}></label>
  <label>Font <input bind:value={font}></label>
  <label>Logo <input type="file" accept="image/png,image/jpeg" on:change={handleFile}></label>
  <button on:click={submit}>Kaydet ve Devam</button>
</main>

<style>
  .layout { max-width: 600px; margin: 40px auto; display: flex; flex-direction: column; gap: 12px; }
  label { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
  input { flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 8px; }
  button { align-self: flex-end; padding: 12px 16px; border: none; background:#3b82f6; color:#fff; border-radius: 8px; }
</style>
