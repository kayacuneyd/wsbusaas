export type OrderStatusKey =
  | 'pending_confirmation'
  | 'payment_received'
  | 'processing'
  | 'completed'
  | 'cancelled'
  | 'failed';

export type OrderStateMeta = {
  key: OrderStatusKey;
  label: string;
  badgeLabel?: string;
  badgeClass: string;
  messages: {
    tr: string;
    en: string;
  };
  description?: string;
  terminal?: boolean;
};

export const ORDER_STATES: OrderStateMeta[] = [
  {
    key: 'pending_confirmation',
    label: 'Onay Bekleniyor',
    badgeLabel: 'Bekliyor',
    badgeClass: 'bg-amber-100 text-amber-800',
    messages: {
      en: "Your transaction is pending confirmation. We're waiting for it to be reflected in our system.",
      tr: 'İşleminizin sunucumuza/sistemimize yansıması bekleniyor.'
    },
    description:
      'Müşteri Ruul.io ödeme adımına ulaştı ve kendi sürecini tamamladı. Sistemimiz harici doğrulama e-postasını bekliyor.'
  },
  {
    key: 'payment_received',
    label: 'Ödeme Alındı',
    badgeLabel: 'Ödeme Onaylandı',
    badgeClass: 'bg-blue-100 text-blue-800',
    messages: {
      en: 'We have received your payment via Ruul.io and are preparing your order for production.',
      tr: 'Ruul.io üzerinden ödemeniz onaylandı, siparişiniz hazırlanıyor.'
    },
    description: 'Admin, Ruul.io e-postasını doğruladı ve ödemeyi sisteme işledi.'
  },
  {
    key: 'processing',
    label: 'İşleniyor',
    badgeLabel: 'İşleniyor',
    badgeClass: 'bg-indigo-100 text-indigo-800',
    messages: {
      en: 'Our team is manually verifying and configuring your service details.',
      tr: 'Ekibimiz siparişinizi manuel olarak doğruluyor ve yapılandırıyor.'
    },
    description: 'Domain, hosting ve tema kurulumu gibi fiziksel adımlar yürütülüyor.'
  },
  {
    key: 'completed',
    label: 'Tamamlandı',
    badgeLabel: 'Tamamlandı',
    badgeClass: 'bg-green-100 text-green-800',
    messages: {
      en: 'Your service has been delivered. Thank you for working with us!',
      tr: 'Hizmetiniz tamamlandı, bizimle çalıştığınız için teşekkür ederiz.'
    },
    description: 'Tüm adımlar tamamlandı ve teslim bilgileri müşteriye iletildi.',
    terminal: true
  },
  {
    key: 'cancelled',
    label: 'İptal Edildi',
    badgeLabel: 'İptal',
    badgeClass: 'bg-gray-200 text-gray-700',
    messages: {
      en: 'This order was cancelled. Please contact support if you have questions.',
      tr: 'Sipariş iptal edildi. Sorularınız için destek ekibimizle iletişime geçebilirsiniz.'
    },
    description: 'Sipariş müşteri isteğiyle ya da manuel olarak iptal edildi.',
    terminal: true
  },
  {
    key: 'failed',
    label: 'Başarısız',
    badgeLabel: 'Başarısız',
    badgeClass: 'bg-red-100 text-red-800',
    messages: {
      en: 'We could not complete this order. Please contact support to continue.',
      tr: 'Sipariş tamamlanamadı. Devam edebilmek için lütfen destek ekibimizle iletişime geçin.'
    },
    description: 'Ödeme ya da doğrulama problemleri nedeniyle sipariş tamamlanamadı.',
    terminal: true
  }
];

export const ORDER_STATE_LOOKUP = ORDER_STATES.reduce<Record<string, OrderStateMeta>>(
  (acc, state) => {
    acc[state.key] = state;
    return acc;
  },
  {}
);

export const DEFAULT_ORDER_STATE = ORDER_STATES[0];

export function getOrderState(key?: string | null): OrderStateMeta {
  if (!key) return DEFAULT_ORDER_STATE;
  return ORDER_STATE_LOOKUP[key] || DEFAULT_ORDER_STATE;
}

export function getOrderStateIndex(key?: string | null): number {
  const state = getOrderState(key);
  return ORDER_STATES.findIndex((item) => item.key === state.key);
}

export function getOrderProgressPercent(key?: string | null): number {
  const index = getOrderStateIndex(key);
  if (index < 0) return 0;
  return ((index + 1) / ORDER_STATES.length) * 100;
}

export function getOrderStatusBadgeClasses(key?: string | null): string {
  return getOrderState(key).badgeClass;
}

export function getOrderStatusLabel(key?: string | null): string {
  return getOrderState(key).badgeLabel || getOrderState(key).label;
}
