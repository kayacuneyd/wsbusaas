<script lang="ts">
  import { t } from 'svelte-i18n';
  const year = new Date().getFullYear();
</script>

<footer class="bg-brand-dark text-white py-12">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row justify-between items-center gap-6">
      <div class="text-center md:text-left">
        <h3 class="text-xl font-bold mb-2">Bezmidar Sitebuilder</h3>
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
