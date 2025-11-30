<script lang="ts">
  import { t } from 'svelte-i18n';
  const year = new Date().getFullYear();
</script>

<footer class="bg-brand-dark text-white border-t border-gray-700 mt-auto">
  <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
      <div class="col-span-1 md:col-span-1">
        <span class="text-2xl font-bold text-brand-light">BEZMIDAR</span>
        <div class="flex items-center justify-center md:justify-start gap-2 mb-4">
          <img src="/logo.png" alt="Bezmidar Logo" class="h-8 w-8" />
          <h3 class="text-xl font-bold text-white">Bezmidar</h3>
        </div>
        <p class="text-gray-400 text-sm">&copy; {year} {$t('common.rights_reserved')}</p>
      </div>
      
      <div class="flex flex-wrap justify-center gap-6 md:gap-8">
        <a href="/contact" class="text-gray-300 hover:text-white transition-colors">{$t('common.contact')}</a>
        <a href="/impressum" class="text-gray-300 hover:text-white transition-colors">{$t('common.impressum')}</a>
        <a href="/datenschutz" class="text-gray-300 hover:text-white transition-colors">{$t('common.privacy')}</a>
      </div>
    </div>
  </div>
</footer>
