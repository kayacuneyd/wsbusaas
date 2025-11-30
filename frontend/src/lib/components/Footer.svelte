<script lang="ts">
  import { t } from 'svelte-i18n';
  const year = new Date().getFullYear();
</script>

<footer class="bg-brand-dark text-white border-t border-gray-700 mt-auto">
  <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
      <div class="col-span-1 flex flex-col items-center md:items-start">
        <div class="flex items-center gap-2 mb-4">
          <span class="text-2xl font-bold text-brand-light">BEZMIDAR</span>
        </div>
        <p class="text-gray-400 text-sm text-center md:text-left">&copy; {year} {$t('common.rights_reserved')}</p>
      </div>
      
      <div class="flex flex-col md:flex-row justify-center md:justify-end items-center gap-4 md:gap-8">
        <a href="/impressum" class="text-gray-300 hover:text-white transition-colors">{$t('common.impressum')}</a>
        <a href="/datenschutz" class="text-gray-300 hover:text-white transition-colors">{$t('common.privacy')}</a>
        <a href="/contact" class="px-4 py-2 border border-gray-500 rounded-md text-gray-300 hover:text-white hover:border-white transition-all">
          {$t('common.contact')}
        </a>
      </div>
    </div>
  </div>
</footer>
