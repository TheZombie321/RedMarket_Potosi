export const ESTADO_BADGES: Record<string, string> = {
  pendiente: 'bg-yellow-100 text-yellow-800',
  en_preparacion: 'bg-orange-100 text-orange-800',
  listo_despacho: 'bg-blue-100 text-blue-800',
  en_camino: 'bg-blue-100 text-blue-800',
  entregado: 'bg-green-100 text-green-800',
  cancelado: 'bg-red-100 text-red-800',
}

export const LABELS: Record<string, string> = {
  pendiente: 'Pendiente',
  en_preparacion: 'En preparación',
  listo_despacho: 'Listo para despacho',
  en_camino: 'En camino',
  entregado: 'Entregado',
  cancelado: 'Cancelado',
}

export const PASOS = ['pendiente', 'en_preparacion', 'listo_despacho', 'en_camino', 'entregado']

export function estadoBadge(e: string): string {
  return ESTADO_BADGES[e] || 'bg-gray-100 text-gray-800'
}

export function pasoActual(estado: string): number {
  return PASOS.indexOf(estado)
}
