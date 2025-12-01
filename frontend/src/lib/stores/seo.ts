import { API_URL } from '$lib/api';
import { writable } from 'svelte/store';

export interface SEOSettings {
  seo_title: string;
  seo_description: string;
  seo_keywords: string;
  seo_og_image: string;
  seo_og_title: string;
  seo_og_description: string;
}

const defaultSettings: SEOSettings = {
  seo_title: 'Bezmidar Sitebuilder - Profesyonel Web Siteniz Hazır',
  seo_description: 'Domain, hosting ve özel tasarım tek pakette. Teknik bilgi gerektirmez.',
  seo_keywords: 'website, domain, hosting, sitebuilder, bezmidar',
  seo_og_image: '',
  seo_og_title: 'Bezmidar Sitebuilder',
  seo_og_description: 'Profesyonel web sitenizi kolayca oluşturun'
};

export const seoSettings = writable<SEOSettings>(defaultSettings);

export async function loadSEOSettings() {
  try {
    const res = await fetch(`${API_URL}/seo.php`);
    if (res.ok) {
      const data = await res.json();
      if (data.success && data.settings) {
        seoSettings.set({ ...defaultSettings, ...data.settings });
      }
    }
  } catch (e) {
    console.error('Failed to load SEO settings:', e);
  }
}

