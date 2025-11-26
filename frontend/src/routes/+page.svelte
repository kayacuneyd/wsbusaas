<script lang="ts">
  import { Api } from '$lib/api';
  let domain = '';
  let result: any = null;
  let loading = false;
  let error = '';

  const submit = async () => {
    loading = true;
    error = '';
    try {
      result = await Api.checkDomain(domain);
    } catch (err: any) {
      error = err.message;
    } finally {
      loading = false;
    }
  };
</script>

<section class="hero">
  <h1>Domain Checker</h1>
  <p>Uygunluk ve DNS/WWHOIS kontrolleri</p>
  <form on:submit|preventDefault={submit} class="form">
    <input bind:value={domain} placeholder="example.com" required>
    <button type="submit" disabled={loading}>{loading ? 'Kontrol ediliyor' : 'Kontrol et'}</button>
  </form>
  {#if error}<p class="error">{error}</p>{/if}
  {#if result}
    <div class="card">
      <p>Uygun: {result.available ? 'Evet' : 'Hayır'}</p>
      <pre>{JSON.stringify(result.records, null, 2)}</pre>
    </div>
  {/if}
</section>

<style>
  .hero { max-width: 720px; margin: 0 auto; padding: 80px 20px; text-align: center; }
  h1 { font-size: 2.2rem; margin-bottom: 12px; }
  .form { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; }
  input { padding: 10px 12px; min-width: 260px; border: 1px solid #ccc; border-radius: 8px; }
  button { padding: 10px 16px; border: none; background: #3b82f6; color: #fff; border-radius: 8px; }
  .card { text-align: left; margin-top: 20px; border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px; }
  .error { color: #dc2626; }
</style>
