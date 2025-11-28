<script lang="ts">
  import { onMount } from 'svelte';
  import { API_URL } from '$lib/api';
  import { adminAuth } from '$lib/stores/auth';

  let packages: any[] = [];
  let loading = true;
  let showModal = false;
  let editingPackage: any = null;
  let formData = {
    name: '',
    slug: '',
    description: '',
    price: '',
    payment_link: '',
    is_active: true,
    display_order: 0
  };

  onMount(async () => {
    await loadPackages();
  });

  async function loadPackages() {
    try {
      const res = await fetch(`${API_URL}/admin/packages`, {
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

  function openAddModal() {
    editingPackage = null;
    formData = {
      name: '',
      slug: '',
      description: '',
      price: '',
      payment_link: '',
      is_active: true,
      display_order: packages.length
    };
    showModal = true;
  }

  function openEditModal(pkg: any) {
    editingPackage = pkg;
    formData = {
      name: pkg.name,
      slug: pkg.slug,
      description: pkg.description || '',
      price: pkg.price || '',
      payment_link: pkg.payment_link || '',
      is_active: pkg.is_active,
      display_order: pkg.display_order || 0
    };
    showModal = true;
  }

  function closeModal() {
    showModal = false;
    editingPackage = null;
  }

  async function savePackage() {
    try {
      const url = editingPackage 
        ? `${API_URL}/admin/packages`
        : `${API_URL}/admin/packages`;
      const method = editingPackage ? 'PUT' : 'POST';
      
      const payload = {
        ...(editingPackage ? { id: editingPackage.id } : {}),
        ...formData,
        price: formData.price ? parseFloat(formData.price) : null
      };

      const res = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${$adminAuth.token}`
        },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      if (data.success) {
        await loadPackages();
        closeModal();
      } else {
        alert(data.error || 'Kayıt başarısız.');
      }
    } catch (e) {
      alert('Bir hata oluştu.');
    }
  }

  async function deletePackage(id: number) {
    if (!confirm('Bu paketi silmek istediğinize emin misiniz?')) return;

    try {
      const res = await fetch(`${API_URL}/admin/packages`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${$adminAuth.token}`
        },
        body: JSON.stringify({ id })
      });

      const data = await res.json();
      if (data.success) {
        await loadPackages();
      } else {
        alert(data.error || 'Silme başarısız.');
      }
    } catch (e) {
      alert('Bir hata oluştu.');
    }
  }

  function generateSlug(name: string) {
    return name
      .toLowerCase()
      .replace(/ğ/g, 'g')
      .replace(/ü/g, 'u')
      .replace(/ş/g, 's')
      .replace(/ı/g, 'i')
      .replace(/ö/g, 'o')
      .replace(/ç/g, 'c')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  $: formData.slug = formData.slug || generateSlug(formData.name);
</script>

<svelte:head>
  <title>Paketler - Admin - Bezmidar Sitebuilder</title>
</svelte:head>

<div class="flex justify-between items-center mb-6">
  <h1 class="text-2xl font-bold text-gray-900">Paket Yönetimi</h1>
  <button
    on:click={openAddModal}
    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
  >
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Yeni Paket Ekle
  </button>
</div>

{#if loading}
  <div class="text-center py-12">Yükleniyor...</div>
{:else if packages.length === 0}
  <div class="text-center py-12 bg-white rounded-lg shadow">
    <p class="text-gray-500">Henüz paket yok.</p>
  </div>
{:else}
  <div class="bg-white shadow overflow-hidden sm:rounded-md">
    <ul class="divide-y divide-gray-200">
      {#each packages as pkg}
        <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <div class="flex items-center gap-3">
                <h3 class="text-lg font-semibold text-gray-900">{pkg.name}</h3>
                {#if pkg.is_active}
                  <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                {:else}
                  <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Pasif</span>
                {/if}
              </div>
              <p class="mt-1 text-sm text-gray-600">{pkg.description || 'Açıklama yok'}</p>
              <div class="mt-2 flex items-center gap-4 text-sm text-gray-500">
                <span>Fiyat: {pkg.price ? pkg.price + '€' : 'Belirtilmemiş'}</span>
                <span>Slug: {pkg.slug}</span>
                <span>Sıra: {pkg.display_order}</span>
              </div>
              {#if pkg.payment_link}
                <p class="mt-2 text-xs text-blue-600 break-all">Ödeme Linki: {pkg.payment_link}</p>
              {/if}
            </div>
            <div class="flex items-center gap-2 ml-4">
              <button
                on:click={() => openEditModal(pkg)}
                class="px-3 py-1 text-sm text-blue-600 hover:text-blue-800"
              >
                Düzenle
              </button>
              <button
                on:click={() => deletePackage(pkg.id)}
                class="px-3 py-1 text-sm text-red-600 hover:text-red-800"
              >
                Sil
              </button>
            </div>
          </div>
        </li>
      {/each}
    </ul>
  </div>
{/if}

{#if showModal}
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" on:click={closeModal}>
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" on:click|stopPropagation>
      <h3 class="text-lg font-bold text-gray-900 mb-4">
        {editingPackage ? 'Paket Düzenle' : 'Yeni Paket Ekle'}
      </h3>
      
      <form on:submit|preventDefault={savePackage} class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Paket Adı *</label>
          <input
            type="text"
            bind:value={formData.name}
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">Slug *</label>
          <input
            type="text"
            bind:value={formData.slug}
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">Açıklama</label>
          <textarea
            bind:value={formData.description}
            rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          ></textarea>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">Fiyat (€)</label>
          <input
            type="number"
            step="0.01"
            bind:value={formData.price}
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">Ödeme Linki</label>
          <input
            type="url"
            bind:value={formData.payment_link}
            placeholder="https://ruul.space/payment/..."
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        
        <div class="flex items-center gap-4">
          <label class="flex items-center">
            <input
              type="checkbox"
              bind:checked={formData.is_active}
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span class="ml-2 text-sm text-gray-700">Aktif</span>
          </label>
          
          <div>
            <label class="block text-sm font-medium text-gray-700">Sıra</label>
            <input
              type="number"
              bind:value={formData.display_order}
              class="mt-1 block w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
        </div>
        
        <div class="flex justify-end gap-2 pt-4">
          <button
            type="button"
            on:click={closeModal}
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
          >
            İptal
          </button>
          <button
            type="submit"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
          >
            {editingPackage ? 'Güncelle' : 'Ekle'}
          </button>
        </div>
      </form>
    </div>
  </div>
{/if}

