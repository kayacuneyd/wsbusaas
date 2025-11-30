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


  let showCreateModal = false;
  let newPackage = {
    name: '',
    slug: '',
    price: '',
    description: '',
    payment_link: ''
  };

  async function createPackage() {
    if (!newPackage.name || !newPackage.slug) {
      alert('Paket adı ve slug zorunludur.');
      return;
    }

    updating = true;
    try {
      const res = await fetch(`${API_URL}/admin/packages.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${$adminAuth.token}`
        },
        body: JSON.stringify(newPackage)
      });
      const data = await res.json();
      if (data.success) {
        alert('Paket başarıyla oluşturuldu!');
        showCreateModal = false;
        newPackage = { name: '', slug: '', price: '', description: '', payment_link: '' };
        loadPackages();
      } else {
        alert(data.error || 'Oluşturma başarısız.');
      }
    } catch (e) {
      alert('İşlem başarısız.');
    } finally {
      updating = false;
    }
  }
</script>

<svelte:head>
  <title>Paket Yönetimi - Admin - Bezmidar</title>
</svelte:head>

<div class="mb-6 flex justify-between items-center">
  <h1 class="text-2xl font-semibold text-gray-900">Paket ve Ödeme Linkleri</h1>
  <button
    on:click={() => showCreateModal = true}
    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
  >
    Yeni Paket Ekle
  </button>
</div>

{#if showCreateModal}
  <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" on:click={() => showCreateModal = false}></div>
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
        <div>
          <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Yeni Paket Oluştur</h3>
          <div class="mt-4 space-y-4">
            <div>
              <label for="new-name" class="block text-sm font-medium text-gray-700">Paket Adı</label>
              <input type="text" id="new-name" bind:value={newPackage.name} class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
              <label for="new-slug" class="block text-sm font-medium text-gray-700">Slug (URL)</label>
              <input type="text" id="new-slug" bind:value={newPackage.slug} class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="ornek-paket">
            </div>
            <div>
              <label for="new-price" class="block text-sm font-medium text-gray-700">Fiyat</label>
              <input type="number" id="new-price" bind:value={newPackage.price} class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
              <label for="new-desc" class="block text-sm font-medium text-gray-700">Açıklama</label>
              <textarea id="new-desc" bind:value={newPackage.description} rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
            </div>
            <div>
              <label for="new-link" class="block text-sm font-medium text-gray-700">Ödeme Linki</label>
              <input type="text" id="new-link" bind:value={newPackage.payment_link} class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
          </div>
        </div>
        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
          <button type="button" on:click={createPackage} class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
            Oluştur
          </button>
          <button type="button" on:click={() => showCreateModal = false} class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
            İptal
          </button>
        </div>
      </div>
    </div>
  </div>
{/if}

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
