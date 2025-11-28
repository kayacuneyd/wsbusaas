<script lang="ts">
  import { onMount } from 'svelte';
  import { API_URL } from '$lib/api';
  import { adminAuth } from '$lib/stores/auth';

  let messages: any[] = [];
  let loading = true;
  let selectedMessage: any = null;
  let replyText = '';

  onMount(async () => {
    await loadMessages();
  });

  async function loadMessages() {
    try {
      const res = await fetch(`${API_URL}/admin/messages`, {
        headers: { 'Authorization': `Bearer ${$adminAuth.token}` }
      });
      const data = await res.json();
      if (data.success) {
        messages = data.messages;
      }
    } catch (e) {
      console.error(e);
    } finally {
      loading = false;
    }
  }

  function openMessage(message: any) {
    selectedMessage = message;
    replyText = message.reply || '';
    
    // Mark as read if unread
    if (!message.is_read) {
      markAsRead(message.id);
    }
  }

  function closeMessage() {
    selectedMessage = null;
    replyText = '';
  }

  async function markAsRead(id: number) {
    try {
      const res = await fetch(`${API_URL}/admin/messages`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${$adminAuth.token}`
        },
        body: JSON.stringify({ id, is_read: true })
      });

      if (res.ok) {
        await loadMessages();
      }
    } catch (e) {
      console.error(e);
    }
  }

  async function saveReply() {
    if (!selectedMessage || !replyText.trim()) return;

    try {
      const res = await fetch(`${API_URL}/admin/messages`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${$adminAuth.token}`
        },
        body: JSON.stringify({ id: selectedMessage.id, reply: replyText })
      });

      const data = await res.json();
      if (data.success) {
        await loadMessages();
        selectedMessage.reply = replyText;
        selectedMessage.replied_at = new Date().toISOString();
        alert('Cevap kaydedildi!');
      } else {
        alert(data.error || 'Cevap kaydedilemedi.');
      }
    } catch (e) {
      alert('Bir hata oluştu.');
    }
  }

  function formatDate(value?: string) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleString('tr-TR');
  }

  $: unreadCount = messages.filter(m => !m.is_read).length;
</script>

<svelte:head>
  <title>Mesajlar - Admin - Bezmidar Sitebuilder</title>
</svelte:head>

<div class="flex justify-between items-center mb-6">
  <div>
    <h1 class="text-2xl font-bold text-gray-900">İletişim Mesajları</h1>
    {#if unreadCount > 0}
      <p class="text-sm text-gray-600 mt-1">{unreadCount} okunmamış mesaj</p>
    {/if}
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  <div class="bg-white shadow overflow-hidden sm:rounded-md">
    {#if loading}
      <div class="text-center py-12">Yükleniyor...</div>
    {:else if messages.length === 0}
      <div class="text-center py-12">
        <p class="text-gray-500">Henüz mesaj yok.</p>
      </div>
    {:else}
      <ul class="divide-y divide-gray-200 max-h-[600px] overflow-y-auto">
        {#each messages as message}
          <li
            class="px-4 py-4 sm:px-6 hover:bg-gray-50 cursor-pointer {!message.is_read ? 'bg-blue-50' : ''}"
            on:click={() => openMessage(message)}
          >
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <p class="text-sm font-semibold text-gray-900 truncate">
                    {message.name}
                  </p>
                  {#if !message.is_read}
                    <span class="flex-shrink-0 inline-block w-2 h-2 bg-blue-600 rounded-full"></span>
                  {/if}
                  {#if message.reply}
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Cevaplandı</span>
                  {/if}
                </div>
                <p class="mt-1 text-sm text-gray-600 truncate">{message.email}</p>
                {#if message.subject}
                  <p class="mt-1 text-sm font-medium text-gray-900 truncate">{message.subject}</p>
                {/if}
                <p class="mt-1 text-xs text-gray-500">{formatDate(message.created_at)}</p>
              </div>
            </div>
          </li>
        {/each}
      </ul>
    {/if}
  </div>

  {#if selectedMessage}
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-900">Mesaj Detayı</h3>
          <button
            on:click={closeMessage}
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <div class="px-4 py-5 sm:px-6 space-y-4">
        <div>
          <p class="text-sm font-medium text-gray-500">Gönderen</p>
          <p class="mt-1 text-sm text-gray-900">{selectedMessage.name}</p>
          <p class="text-sm text-blue-600">{selectedMessage.email}</p>
        </div>

        {#if selectedMessage.subject}
          <div>
            <p class="text-sm font-medium text-gray-500">Konu</p>
            <p class="mt-1 text-sm text-gray-900">{selectedMessage.subject}</p>
          </div>
        {/if}

        <div>
          <p class="text-sm font-medium text-gray-500">Mesaj</p>
          <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{selectedMessage.message}</p>
        </div>

        <div>
          <p class="text-sm font-medium text-gray-500">Tarih</p>
          <p class="mt-1 text-sm text-gray-900">{formatDate(selectedMessage.created_at)}</p>
        </div>

        {#if selectedMessage.reply}
          <div class="pt-4 border-t border-gray-200">
            <p class="text-sm font-medium text-gray-500 mb-2">Cevap</p>
            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
              <p class="text-sm text-gray-900 whitespace-pre-wrap">{selectedMessage.reply}</p>
              <p class="mt-2 text-xs text-gray-500">Cevaplanma: {formatDate(selectedMessage.replied_at)}</p>
            </div>
          </div>
        {/if}

        <div class="pt-4 border-t border-gray-200">
          <label class="block text-sm font-medium text-gray-700 mb-2">Cevap Yaz</label>
          <textarea
            bind:value={replyText}
            rows="4"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            placeholder="Mesaja cevap yazın..."
          ></textarea>
          <button
            on:click={saveReply}
            class="mt-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
          >
            {selectedMessage.reply ? 'Cevabı Güncelle' : 'Cevap Gönder'}
          </button>
        </div>
      </div>
    </div>
  {/if}
</div>

