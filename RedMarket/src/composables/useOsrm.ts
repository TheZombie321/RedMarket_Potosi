const OSRM_BASE = 'https://router.project-osrm.org/route/v1'

export interface RouteResult {
  geometry: { coordinates: [number, number][] }
  distance: number
  duration: number
}

export async function fetchRoute(
  start: [number, number],
  end: [number, number],
  signal?: AbortSignal
): Promise<RouteResult | null> {
  const url = `${OSRM_BASE}/driving/${start[1]},${start[0]};${end[1]},${end[0]}?geometries=geojson&overview=full`

  try {
    const res = await fetch(url, { signal })
    if (!res.ok) return null
    const data = await res.json()
    if (!data.routes?.[0]) return null

    const route = data.routes[0]
    return {
      geometry: route.geometry,
      distance: route.distance / 1000,
      duration: route.duration / 60,
    }
  } catch {
    return null
  }
}

export function haversineDistance(
  lat1: number, lng1: number,
  lat2: number, lng2: number
): number {
  const R = 6371
  const dLat = (lat2 - lat1) * Math.PI / 180
  const dLng = (lng2 - lng1) * Math.PI / 180
  const a = Math.sin(dLat / 2) ** 2
    + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180)
    * Math.sin(dLng / 2) ** 2
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
}

export function calcularDeliveryFee(lat?: number | null, lng?: number | null): number {
  const STORE_LAT = -19.5836
  const STORE_LNG = -65.7531
  const BASE_FEE = 10
  const BASE_KM = 3
  const EXTRA_PER_KM = 1
  const MAX_FEE = 15

  if (!lat || !lng || lat === 0 || lng === 0) return BASE_FEE

  const dist = haversineDistance(STORE_LAT, STORE_LNG, lat, lng)
  if (dist <= BASE_KM) return BASE_FEE

  const extraKm = Math.ceil(dist - BASE_KM)
  return Math.min(BASE_FEE + EXTRA_PER_KM * extraKm, MAX_FEE)
}
