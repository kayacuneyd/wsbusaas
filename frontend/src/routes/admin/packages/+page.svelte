<script lang="ts">
  import { onMount } from 'svelte';
  import { API_URL } from '$lib/api';
  import { adminAuth } from '$lib/stores/auth';

  let packages: any[] = [];
  let loading = true;
  let updating = false;

  async function loadPackages() {
    try {
      const res = await fetch(`${API_URL}/admin/packages.php`, {
        headers: { 'Authorization': `Bearer ${$adminAuth.token}` }
      });
      const data = await res.json();
      if (data.success) {
        packages = data.packages;
      }
    } catch (e) {
      console.error(e);
    } finally {
      loading = false;
    }
  }

  async function updatePackage(pkg: any) {
    if (!confirm(`${pkg.name} paketini güncellemek istediğinize emin misiniz?`)) return;

    updating = true;
    try {
      const res = await fetch(`${API_URL}/admin/packages.php`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${$adminAuth.token}`
        },
        body: JSON.stringify(pkg)
      });
      const data = await res.json();
      if (data.success) {
        alert('Paket başarıyla güncellendi!');
      } else {
        alert(data.error || 'Güncelleme başarısız.');
      }
    } catch (e) {
      alert('İşlem başarısız.');
    } finally {
      updating = false;
    }
  }

  onMount(loadPackages);
</script>

<svelte:head>
  <title>Paket Yönetimi - Admin - Bezmidar</title>
</svelte:head>

<div class="mb-6 flex justify-between items-center">
  <h1 class="text-2xl font-semibold text-gray-900">Paket ve Ödeme Linkleri</h1>
</div>

{#if loading}
  <div class="text-center">Yükleniyor...</div>
{:else}
  <div class="bg-white shadow overflow-hidden sm:rounded-md">
    <ul class="divide-y divide-gray-200">
      {#each packages as pkg}
        <li class="px-4 py-4 sm:px-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">{pkg.name}</h3>
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
              {pkg.price} {pkg.currency ?? 'EUR'}
            </span>
          </div>
          
          <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <div class="sm:col-span-6">
              <label for="payment_link_{pkg.id}" class="block text-sm font-medium text-gray-700">
                Ruul.io Ödeme Linki
              </label>
              <div class="mt-1 flex rounded-md shadow-sm">
                <input
                  type="text"
                  id="payment_link_{pkg.id}"
                  bind:value={pkg.payment_link}
                  class="flex-1 block w-full rounded-none rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  placeholder="https://ruul.io/pay/..."
                />
                <button
                  type="button"
                  on:click={() => updatePackage(pkg)}
                  disabled={updating}
                  class="inline-flex items-center rounded-r-md border border-l-0 border-gray-300 bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                >
                  {updating ? '...' : 'Kaydet'}
                </button>
              </div>
              <p class="mt-2 text-xs text-gray-500">
                Bu link, müşteriler "Satın Al" butonuna tıkladığında yönlendirilecekleri adrestir. Ruul.io linki değiştikçe buradan güncelleyebilirsiniz.
              </p>
            </div>
          </div>
        </li>
      {/each}
    </ul>
  </div>
{/if}
