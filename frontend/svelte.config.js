import adapter from '@sveltejs/adapter-vercel';

const config = {
  kit: {
    adapter: adapter(),
    alias: {
      '$lib': 'src/lib'
    }
  }
};

export default config;
