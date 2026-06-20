<script setup lang="ts">
import { ref, onMounted, reactive, computed, watch } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'
import { apiFetch } from '../composables/useApi'
import Paginador from '../components/Paginador.vue'

const auth = useAuthStore()
const toast = useToastStore()
const tab = ref<'dashboard' | 'productos' | 'pedidos' | 'usuarios'>('dashboard')
const cargando = ref(true)
const error = ref('')

// ── Dashboard ────────────────────────────────────────────
const dashboard = reactive({
  pedidos_hoy: 0,
  ingresos_hoy: 0,
  ingresos_mes: 0,
  top_productos: [] as any[],
  pedidos_por_estado: {} as Record<string, number>,
  stock_bajo: [] as any[],
  total_clientes: 0,
  total_productos: 0,
  total_staff: 0,
})

const fetchDashboard = async () => {
  try {
    const data = await apiFetch<any>('/dashboard')
    Object.assign(dashboard, data)
  } catch (e) { console.error(e) }
}

// ── Productos ────────────────────────────────────────────
const productos = ref<any[]>([])
const busqueda = ref('')
const paginaProductos = ref(1)
const ultPagProductos = ref(1)

const fetchProductos = async () => {
  try {
    const params = new URLSearchParams()
    if (busqueda.value.trim()) params.set('search', busqueda.value.trim())
    params.set('page', String(paginaProductos.value))
    params.set('per_page', '15')
    const resp = await apiFetch<any>(`/productos?${params}`)
    productos.value = resp.data ?? resp
    if (resp.meta) {
      paginaProductos.value = resp.meta.current_page
      ultPagProductos.value = resp.meta.last_page
    }
  } catch (e) { console.error(e) }
}

let timerProductos: ReturnType<typeof setTimeout>
watch(busqueda, () => {
  clearTimeout(timerProductos)
  timerProductos = setTimeout(() => { paginaProductos.value = 1; fetchProductos() }, 300)
})

// Modal producto
const showModal = ref(false)
const editando = ref<any>(null)
const form = ref<any>({
  imagen: null, nombre: '', descripcion: '', codigo_barras: '',
  precio_compra: 0, precio_venta: 0, precio_oferta: 0, en_descuento: false,
  stock_actual: 0, stock_minimo: 0, pasillo: '', nivel: '',
  unidad_medida: 'un', es_perecedero: false, fecha_vencimiento: '',
  categoria_id: '', proveedor_id: '',
})
const imagenPreview = ref<string | null>(null)
const categorias = ref<any[]>([])
const proveedores = ref<any[]>([])

const fetchCategorias = async () => {
  try {
    const data = await apiFetch<any>('/categorias')
    categorias.value = Array.isArray(data) ? data : data.data ?? []
  } catch (e) { console.error(e) }
}

const fetchProveedores = async () => {
  try {
    const data = await apiFetch<any>('/proveedores')
    proveedores.value = Array.isArray(data) ? data : data.data ?? []
  } catch (e) { console.error(e) }
}

const resetForm = () => {
  form.value = {
    imagen: null, nombre: '', descripcion: '', codigo_barras: '',
    precio_compra: 0, precio_venta: 0, precio_oferta: 0, en_descuento: false,
    stock_actual: 0, stock_minimo: 0, pasillo: '', nivel: '',
    unidad_medida: 'un', es_perecedero: false, fecha_vencimiento: '',
    categoria_id: '', proveedor_id: '',
  }
  imagenPreview.value = null
  editando.value = null
}

const openCreate = () => { resetForm(); showModal.value = true }

const openEdit = (p: any) => {
  editando.value = p
  form.value = {
    imagen: null, nombre: p.nombre, descripcion: p.descripcion || '',
    codigo_barras: p.codigo_barras || '', precio_compra: p.precio_compra || 0,
    precio_venta: p.precio_venta || 0, precio_oferta: p.precio_oferta || 0,
    en_descuento: !!p.en_descuento, stock_actual: p.stock_actual || 0,
    stock_minimo: p.stock_minimo || 0, pasillo: p.pasillo || '', nivel: p.nivel || '',
    unidad_medida: p.unidad_medida || 'un', es_perecedero: !!p.es_perecedero,
    fecha_vencimiento: p.fecha_vencimiento || '', categoria_id: p.categoria_id || '',
    proveedor_id: '',
  }
  imagenPreview.value = p.imagen_url || null
  showModal.value = true
}

const closeModal = () => { showModal.value = false; resetForm() }

const onFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files?.length) {
    form.value.imagen = target.files[0]
    const reader = new FileReader()
    reader.onload = () => { imagenPreview.value = reader.result as string }
    reader.readAsDataURL(target.files[0])
  }
}

const saveProducto = async () => {
  try {
    const fd = new FormData()
    if (form.value.imagen) fd.append('imagen', form.value.imagen as Blob)
    fd.append('nombre', form.value.nombre)
    fd.append('descripcion', form.value.descripcion)
    fd.append('codigo_barras', form.value.codigo_barras)
    fd.append('precio_compra', String(form.value.precio_compra))
    fd.append('precio_venta', String(form.value.precio_venta))
    fd.append('precio_oferta', String(form.value.precio_oferta || 0))
    fd.append('en_descuento', form.value.en_descuento ? '1' : '0')
    fd.append('stock_actual', String(form.value.stock_actual))
    fd.append('stock_minimo', String(form.value.stock_minimo))
    fd.append('pasillo', form.value.pasillo)
    fd.append('nivel', form.value.nivel)
    fd.append('unidad_medida', form.value.unidad_medida)
    fd.append('es_perecedero', form.value.es_perecedero ? '1' : '0')
    fd.append('fecha_vencimiento', form.value.fecha_vencimiento)
    fd.append('categoria_id', String(form.value.categoria_id))
    if (!editando.value) fd.append('proveedor_id', String(form.value.proveedor_id))

    let url = '/productos'
    let method = 'POST'
    if (editando.value) { url += '/' + editando.value.id; method = 'POST'; fd.append('_method', 'PUT') }

    await apiFetch(url, { method, body: fd })
    closeModal()
    await fetchProductos()
    toast.add(editando.value ? 'Producto actualizado' : 'Producto creado', 'success')
  } catch (e: any) { toast.add('Error al guardar: ' + e.message, 'error') }
}

const eliminarProducto = async (p: any) => {
  if (!confirm(`¿Eliminar "${p.nombre}"?`)) return
  try {
    await apiFetch(`/productos/${p.id}`, { method: 'DELETE' })
    await fetchProductos()
    toast.add('Producto eliminado', 'success')
  } catch (e: any) { toast.add('Error: ' + e.message, 'error') }
}

const toggleDescuento = async (p: any) => {
  try {
    await apiFetch(`/productos/${p.id}`, {
      method: 'PUT',
      body: JSON.stringify({ en_descuento: !p.en_descuento }),
    })
    p.en_descuento = !p.en_descuento
  } catch (e: any) { toast.add('Error: ' + e.message, 'error') }
}

const puedeCrear = computed(() => auth.user?.roles?.some((r: any) => ['Administrador', 'Encargado'].includes(r.name ?? r)))
const puedeEliminar = computed(() => auth.user?.roles?.some((r: any) => (r.name ?? r) === 'Administrador'))

// ── Usuarios ────────────────────────────────────────────
const usuarios = ref<any[]>([])
const paginaUsuarios = ref(1)
const ultPagUsuarios = ref(1)
const showUserModal = ref(false)
const userEditando = ref<any>(null)
const userForm = ref({ name: '', email: '', password: '', password_confirmation: '', telefono: '', direccion: '', role: 'Picking' })
const staffRoles = ['Administrador', 'Encargado', 'Picking', 'Repartidor']

const fetchUsuarios = async () => {
  try {
    const resp = await apiFetch<any>(`/usuarios?page=${paginaUsuarios.value}&per_page=15`)
    usuarios.value = resp.data ?? resp
    if (resp.meta) { paginaUsuarios.value = resp.meta.current_page; ultPagUsuarios.value = resp.meta.last_page }
  } catch (e) { console.error(e) }
}

const resetUserForm = () => { userForm.value = { name: '', email: '', password: '', password_confirmation: '', telefono: '', direccion: '', role: 'Picking' }; userEditando.value = null }
const openUserCreate = () => { resetUserForm(); showUserModal.value = true }

const openUserEdit = (u: any) => {
  userEditando.value = u
  userForm.value = { name: u.name, email: u.email, password: '', password_confirmation: '', telefono: u.telefono || '', direccion: u.direccion || '', role: u.roles?.[0]?.name || u.roles?.[0] || 'Picking' }
  showUserModal.value = true
}

const closeUserModal = () => { showUserModal.value = false; resetUserForm() }

const saveUser = async () => {
  try {
    const body: any = { name: userForm.value.name, email: userForm.value.email, role: userForm.value.role }
    if (userForm.value.telefono) body.telefono = userForm.value.telefono
    if (userForm.value.direccion) body.direccion = userForm.value.direccion

    let url = '/usuarios'
    let method = 'POST'
    if (userEditando.value) { url += '/' + userEditando.value.id; method = 'PUT'; if (userForm.value.password) body.password = userForm.value.password }
    else body.password = userForm.value.password

    await apiFetch(url, { method, body: JSON.stringify(body) })
    closeUserModal()
    await fetchUsuarios()
    toast.add(userEditando.value ? 'Usuario actualizado' : 'Usuario creado', 'success')
  } catch (e: any) { toast.add('Error al guardar usuario: ' + e.message, 'error') }
}

const eliminarUsuario = async (u: any) => {
  if (!confirm(`¿Eliminar a "${u.name}"?`)) return
  try {
    await apiFetch(`/usuarios/${u.id}`, { method: 'DELETE' })
    await fetchUsuarios()
  } catch (e: any) { toast.add('Error: ' + e.message, 'error') }
}

// ── Pedidos ──────────────────────────────────────────────
const pedidos = ref<any[]>([])
const filtroEstado = ref('')
const busquedaPedido = ref('')
const paginaPedidos = ref(1)
const ultPagPedidos = ref(1)

const fetchPedidos = async () => {
  try {
    const params = new URLSearchParams()
    if (filtroEstado.value) params.set('estado', filtroEstado.value)
    if (busquedaPedido.value.trim()) params.set('search', busquedaPedido.value.trim())
    params.set('page', String(paginaPedidos.value))
    params.set('per_page', '15')
    const resp = await apiFetch<any>(`/pedidos?${params}`)
    pedidos.value = resp.data ?? resp
    if (resp.meta) { paginaPedidos.value = resp.meta.current_page; ultPagPedidos.value = resp.meta.last_page }
  } catch (e) { console.error(e) }
}

let timerPedidos: ReturnType<typeof setTimeout>
watch(busquedaPedido, () => {
  clearTimeout(timerPedidos)
  timerPedidos = setTimeout(() => { paginaPedidos.value = 1; fetchPedidos() }, 300)
})
watch(filtroEstado, () => { paginaPedidos.value = 1; fetchPedidos() })

const cambiarEstado = async (pedido: any, nuevoEstado: string) => {
  try {
    const data = await apiFetch<any>(`/pedidos/${pedido.id}`, {
      method: 'PUT',
      body: JSON.stringify({ estado: nuevoEstado }),
    })
    Object.assign(pedido, data)
    toast.add(`Pedido ${data.codigo || '#' + data.id} → ${nuevoEstado.replace(/_/g, ' ')}`, 'success')
  } catch (e: any) { toast.add('Error: ' + e.message, 'error') }
}

const estados = ['pendiente', 'en_preparacion', 'listo_despacho', 'en_camino', 'entregado', 'cancelado']

const estadoBadge = (e: string) => {
  const colors: Record<string, string> = {
    pendiente: 'bg-yellow-100 text-yellow-800', en_preparacion: 'bg-orange-100 text-orange-800',
    listo_despacho: 'bg-blue-100 text-blue-800', en_camino: 'bg-purple-100 text-purple-800',
    entregado: 'bg-green-100 text-green-800', cancelado: 'bg-red-100 text-red-800',
  }
  return colors[e] || 'bg-gray-100 text-gray-800'
}

const loadData = async () => {
  cargando.value = true
  error.value = ''
  try {
    await Promise.all([
      fetchDashboard().catch(() => {}),
      fetchProductos().catch(() => {}),
      fetchPedidos().catch(() => {}),
      fetchCategorias().catch(() => {}),
      fetchProveedores().catch(() => {}),
      fetchUsuarios().catch(() => {}),
    ])
  } catch {
    error.value = 'Error al cargar datos'
  } finally {
    cargando.value = false
  }
}

onMounted(loadData)
</script>

<template>
  <div>
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Panel de Administración</h2>

    <div class="flex gap-2 mb-6 flex-wrap">
      <button @click="tab = 'dashboard'"
        :class="tab === 'dashboard' ? 'bg-red-700 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
        class="px-4 py-2 rounded text-sm font-semibold cursor-pointer border-none">Dashboard</button>
      <button @click="tab = 'productos'"
        :class="tab === 'productos' ? 'bg-red-700 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
        class="px-4 py-2 rounded text-sm font-semibold cursor-pointer border-none">Productos</button>
      <button @click="tab = 'pedidos'"
        :class="tab === 'pedidos' ? 'bg-red-700 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
        class="px-4 py-2 rounded text-sm font-semibold cursor-pointer border-none">Pedidos</button>
      <button @click="tab = 'usuarios'"
        :class="tab === 'usuarios' ? 'bg-red-700 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
        class="px-4 py-2 rounded text-sm font-semibold cursor-pointer border-none">Usuarios</button>
    </div>

    <div v-if="cargando" class="py-8 space-y-4">
      <div class="skeleton h-6 w-48 mx-auto"></div>
      <div class="grid grid-cols-4 gap-4">
        <div v-for="i in 4" :key="i" class="skeleton h-24"></div>
      </div>
      <div class="skeleton h-48 w-full"></div>
    </div>

    <div v-else-if="error" class="text-center py-12">
      <p class="text-red-700 mb-3">{{ error }}</p>
      <button @click="loadData" class="bg-red-700 text-white px-4 py-2 rounded-lg cursor-pointer border-none hover:bg-red-800">Reintentar</button>
    </div>

    <!-- DASHBOARD TAB -->
    <div v-else-if="tab === 'dashboard'">
      <!-- Metrics cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Pedidos Hoy</p>
          <p class="text-3xl font-bold text-gray-800 mt-1">{{ dashboard.pedidos_hoy }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-500">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Ingresos Hoy</p>
          <p class="text-3xl font-bold text-green-600 mt-1">Bs. {{ dashboard.ingresos_hoy.toFixed(2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-purple-500">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Ingresos del Mes</p>
          <p class="text-3xl font-bold text-gray-800 mt-1">Bs. {{ dashboard.ingresos_mes.toFixed(2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-amber-500">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Clientes</p>
          <p class="text-3xl font-bold text-gray-800 mt-1">{{ dashboard.total_clientes }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Top productos -->
        <div class="bg-white rounded-lg shadow p-5">
          <h3 class="font-semibold text-gray-800 mb-3">🏆 Top 5 Productos más vendidos</h3>
          <div v-if="dashboard.top_productos.length === 0" class="text-center py-6 text-gray-400 text-sm">Sin datos aún.</div>
          <div v-else class="space-y-2">
            <div v-for="(p, i) in dashboard.top_productos" :key="p.id"
              class="flex items-center gap-3 py-2 border-b border-gray-100 last:border-0">
              <span class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center"
                :class="i === 0 ? 'bg-yellow-100 text-yellow-700' : i === 1 ? 'bg-gray-200 text-gray-600' : i === 2 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500'">{{ i + 1 }}</span>
              <span class="flex-1 text-sm font-medium text-gray-700">{{ p.nombre }}</span>
              <span class="text-sm font-bold text-gray-800">{{ p.total_vendido }} vendidos</span>
            </div>
          </div>
        </div>

        <!-- Pedidos por estado -->
        <div class="bg-white rounded-lg shadow p-5">
          <h3 class="font-semibold text-gray-800 mb-3">📊 Pedidos por Estado</h3>
          <div class="space-y-2">
            <div v-for="(total, estado) in dashboard.pedidos_por_estado" :key="estado"
              class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
              <span class="text-sm font-medium px-2 py-0.5 rounded-full" :class="estadoBadge(estado)">{{ estado.replace(/_/g, ' ') }}</span>
              <span class="text-sm font-bold text-gray-800">{{ total }}</span>
            </div>
            <div v-if="Object.keys(dashboard.pedidos_por_estado).length === 0" class="text-center py-6 text-gray-400 text-sm">Sin pedidos.</div>
          </div>
        </div>
      </div>

      <!-- Stock bajo -->
      <div class="bg-white rounded-lg shadow p-5">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-semibold text-gray-800">⚠️ Productos con stock bajo</h3>
          <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-semibold">{{ dashboard.stock_bajo.length }} alertas</span>
        </div>
        <div v-if="dashboard.stock_bajo.length === 0" class="text-center py-6 text-green-500 text-sm">✅ Todos los productos tienen stock suficiente.</div>
        <div v-else>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr><th class="text-left px-3 py-2">Producto</th><th class="text-right px-3 py-2">Stock Actual</th><th class="text-right px-3 py-2">Stock Mínimo</th></tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="p in dashboard.stock_bajo" :key="p.id" class="hover:bg-red-50">
                  <td class="px-3 py-2 font-medium text-gray-700">{{ p.nombre }}</td>
                  <td class="px-3 py-2 text-right font-bold" :class="p.stock_actual === 0 ? 'text-red-600' : 'text-orange-600'">{{ p.stock_actual }} {{ p.unidad_medida }}</td>
                  <td class="px-3 py-2 text-right text-gray-500">{{ p.stock_minimo }} {{ p.unidad_medida }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Resumen -->
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-6">
        <div class="bg-gray-50 rounded-lg p-4 text-center">
          <p class="text-2xl font-bold text-gray-800">{{ dashboard.total_productos }}</p>
          <p class="text-xs text-gray-500">Productos</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-4 text-center">
          <p class="text-2xl font-bold text-gray-800">{{ dashboard.total_staff }}</p>
          <p class="text-xs text-gray-500">Personal</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-4 text-center">
          <p class="text-2xl font-bold text-gray-800">{{ Object.values(dashboard.pedidos_por_estado).reduce((a: number, b: any) => a + (Number(b) || 0), 0) }}</p>
          <p class="text-xs text-gray-500">Total Pedidos</p>
        </div>
      </div>
    </div>

    <!-- PRODUCTOS TAB -->
    <div v-else-if="tab === 'productos'">
      <div class="flex justify-between items-center mb-3 gap-3">
        <input v-model="busqueda" placeholder="Buscar productos…"
          class="flex-1 max-w-xs border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" />
        <button v-if="puedeCrear" @click="openCreate"
          class="bg-red-700 text-white px-4 py-2 rounded text-sm font-semibold cursor-pointer border-none hover:bg-red-800 shrink-0">+ Nuevo Producto</button>
      </div>
      <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
            <tr><th class="text-left px-4 py-3">ID</th><th class="text-left px-4 py-3">Imagen</th><th class="text-left px-4 py-3">Nombre</th><th class="text-right px-4 py-3">P. Venta</th><th class="text-right px-4 py-3">Stock</th><th class="text-center px-4 py-3">Desc.</th><th class="text-center px-4 py-3">Categoría</th><th class="text-center px-4 py-3" colspan="2">Acciones</th></tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="p in productos" :key="p.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-gray-500">{{ p.id }}</td>
              <td class="px-4 py-3"><img v-if="p.imagen_url" :src="p.imagen_url" loading="lazy" class="w-10 h-10 object-cover rounded" /><span v-else class="text-gray-300">—</span></td>
              <td class="px-4 py-3 font-medium max-w-48 truncate">{{ p.nombre }}</td>
              <td class="px-4 py-3 text-right">Bs. {{ p.precio_venta }}</td>
              <td class="px-4 py-3 text-right"><span :class="p.stock_actual < 10 ? 'text-red-600 font-bold' : ''">{{ p.stock_actual }}</span></td>
              <td class="px-4 py-3 text-center">
                <button @click="toggleDescuento(p)" class="text-xs px-2 py-1 rounded cursor-pointer border-none"
                  :class="p.en_descuento ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">{{ p.en_descuento ? 'Activo' : 'Inactivo' }}</button>
              </td>
              <td class="px-4 py-3 text-center text-gray-500">{{ p.categoria?.nombre }}</td>
              <td class="px-4 py-3 text-center"><button v-if="puedeCrear" @click="openEdit(p)" class="text-blue-600 hover:text-blue-800 text-sm cursor-pointer border-none bg-transparent">Editar</button></td>
              <td class="px-4 py-3 text-center"><button v-if="puedeEliminar" @click="eliminarProducto(p)" class="text-red-600 hover:text-red-800 text-sm cursor-pointer border-none bg-transparent">Eliminar</button></td>
            </tr>
          </tbody>
        </table>
      </div>
      <Paginador v-if="ultPagProductos > 1" :actual="paginaProductos" :total="ultPagProductos" @anterior="paginaProductos--; fetchProductos()" @siguiente="paginaProductos++; fetchProductos()" />
    </div>

    <!-- PEDIDOS TAB -->
    <div v-else-if="tab === 'pedidos'">
      <div class="flex flex-wrap items-center gap-3 mb-4">
        <input v-model="busquedaPedido" placeholder="Buscar por cliente…"
          class="flex-1 min-w-48 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" />
        <label class="text-sm font-medium text-gray-600">Estado:</label>
        <select v-model="filtroEstado" class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
          <option value="">Todos</option>
          <option v-for="e in estados" :key="e" :value="e">{{ e.replace(/_/g, ' ') }}</option>
        </select>
      </div>
      <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
            <tr><th class="text-left px-4 py-3">#</th><th class="text-left px-4 py-3">Cliente</th><th class="text-left px-4 py-3">Items</th><th class="text-right px-4 py-3">Total</th><th class="text-center px-4 py-3">Estado</th><th class="text-left px-4 py-3">Preparó</th><th class="text-left px-4 py-3">Entregó</th><th class="text-left px-4 py-3">Dirección</th><th class="text-center px-4 py-3">Acción</th></tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="p in pedidos" :key="p.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-bold text-red-700">{{ p.codigo || p.id }}</td>
              <td class="px-4 py-3">{{ p.user?.name || p.user_id }}</td>
              <td class="px-4 py-3 text-xs text-gray-500">{{ p.items?.length }} prod.</td>
              <td class="px-4 py-3 text-right font-medium">Bs. {{ p.total_final }}</td>
              <td class="px-4 py-3 text-center"><span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="estadoBadge(p.estado)">{{ p.estado.replace(/_/g, ' ') }}</span></td>
              <td class="px-4 py-3 text-xs text-gray-500">{{ p.preparador?.name || '—' }}</td>
              <td class="px-4 py-3 text-xs text-gray-500">{{ p.repartidor?.name || '—' }}</td>
              <td class="px-4 py-3 max-w-32 truncate text-gray-500 text-xs" :title="p.direccion_texto">{{ p.direccion_texto || '—' }}</td>
              <td class="px-4 py-3 text-center">
                <select @change="cambiarEstado(p, ($event.target as HTMLSelectElement).value)"
                  class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-red-300">
                  <option value="" disabled selected>Cambiar</option>
                  <option v-for="e in estados" :key="e" :value="e" :disabled="e === p.estado">{{ e.replace(/_/g, ' ') }}</option>
                </select>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <Paginador v-if="ultPagPedidos > 1" :actual="paginaPedidos" :total="ultPagPedidos" @anterior="paginaPedidos--; fetchPedidos()" @siguiente="paginaPedidos++; fetchPedidos()" />
      <p class="text-xs text-gray-400 mt-2">{{ pedidos.length }} pedidos</p>
    </div>

    <!-- USUARIOS TAB -->
    <div v-else>
      <div class="flex justify-between items-center mb-3">
        <p class="text-xs text-gray-400">{{ usuarios.length }} usuarios (personal)</p>
        <button v-if="puedeCrear" @click="openUserCreate"
          class="bg-red-700 text-white px-4 py-2 rounded text-sm font-semibold cursor-pointer border-none hover:bg-red-800">+ Nuevo Personal</button>
      </div>
      <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
            <tr><th class="text-left px-4 py-3">ID</th><th class="text-left px-4 py-3">Nombre</th><th class="text-left px-4 py-3">Email</th><th class="text-left px-4 py-3">Teléfono</th><th class="text-center px-4 py-3">Rol</th><th class="text-center px-4 py-3" colspan="2">Acciones</th></tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="u in usuarios" :key="u.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-gray-500">{{ u.id }}</td>
              <td class="px-4 py-3 font-medium">{{ u.name }}</td>
              <td class="px-4 py-3 text-gray-600">{{ u.email }}</td>
              <td class="px-4 py-3 text-gray-500 text-xs">{{ u.telefono || '—' }}</td>
              <td class="px-4 py-3 text-center">
                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                  :class="{ 'bg-red-100 text-red-800': (u.roles?.[0]?.name || u.roles?.[0]) === 'Administrador', 'bg-purple-100 text-purple-800': (u.roles?.[0]?.name || u.roles?.[0]) === 'Encargado', 'bg-amber-100 text-amber-800': (u.roles?.[0]?.name || u.roles?.[0]) === 'Picking', 'bg-blue-100 text-blue-800': (u.roles?.[0]?.name || u.roles?.[0]) === 'Repartidor' }">
                  {{ u.roles?.[0]?.name || u.roles?.[0] }}
                </span>
              </td>
              <td class="px-4 py-3 text-center"><button @click="openUserEdit(u)" class="text-blue-600 hover:text-blue-800 text-sm cursor-pointer border-none bg-transparent">Editar</button></td>
              <td class="px-4 py-3 text-center"><button v-if="puedeEliminar" @click="eliminarUsuario(u)" class="text-red-600 hover:text-red-800 text-sm cursor-pointer border-none bg-transparent">Eliminar</button></td>
            </tr>
          </tbody>
        </table>
      </div>
      <Paginador v-if="ultPagUsuarios > 1" :actual="paginaUsuarios" :total="ultPagUsuarios" @anterior="paginaUsuarios--; fetchUsuarios()" @siguiente="paginaUsuarios++; fetchUsuarios()" />
    </div>

    <!-- MODAL PRODUCTO -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="closeModal">
          <div class="modal-panel bg-white rounded-lg shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">{{ editando ? 'Editar' : 'Nuevo' }} Producto</h3>
        <form @submit.prevent="saveProducto" class="space-y-3">
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Imagen</label>
            <div class="flex items-center gap-3">
              <img v-if="imagenPreview" :src="imagenPreview" class="w-16 h-16 object-cover rounded border" />
              <input type="file" accept="image/*" @change="onFileChange" class="text-sm text-gray-600 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="block text-sm font-medium text-gray-600">Nombre *</label><input v-model="form.nombre" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" /></div>
            <div><label class="block text-sm font-medium text-gray-600">Código Barras *</label><input v-model="form.codigo_barras" required :disabled="!!editando" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" /></div>
          </div>
          <div><label class="block text-sm font-medium text-gray-600">Descripción</label><textarea v-model="form.descripcion" rows="2" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"></textarea></div>
          <div class="grid grid-cols-3 gap-3">
            <div><label class="block text-sm font-medium text-gray-600">P. Compra *</label><input v-model.number="form.precio_compra" type="number" step="0.01" min="0" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" /></div>
            <div><label class="block text-sm font-medium text-gray-600">P. Venta *</label><input v-model.number="form.precio_venta" type="number" step="0.01" min="0" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" /></div>
            <div><label class="block text-sm font-medium text-gray-600">P. Oferta</label><input v-model.number="form.precio_oferta" type="number" step="0.01" min="0" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" /></div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="block text-sm font-medium text-gray-600">Stock Actual *</label><input v-model.number="form.stock_actual" type="number" min="0" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" /></div>
            <div><label class="block text-sm font-medium text-gray-600">Stock Mínimo *</label><input v-model.number="form.stock_minimo" type="number" min="0" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" /></div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="block text-sm font-medium text-gray-600">Pasillo *</label><input v-model="form.pasillo" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" /></div>
            <div><label class="block text-sm font-medium text-gray-600">Nivel *</label><input v-model="form.nivel" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" /></div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="block text-sm font-medium text-gray-600">Unidad Medida *</label>
              <select v-model="form.unidad_medida" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                <option value="un">Unidad (un)</option><option value="kg">Kilogramo (kg)</option><option value="lt">Litro (lt)</option><option value="gr">Gramo (gr)</option>
              </select>
            </div>
            <div><label class="block text-sm font-medium text-gray-600">Categoría *</label>
              <select v-model="form.categoria_id" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                <option value="" disabled>Seleccionar</option>
                <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
              </select>
            </div>
          </div>
          <div v-if="!editando"><label class="block text-sm font-medium text-gray-600">Proveedor *</label>
            <select v-model="form.proveedor_id" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
              <option value="" disabled>Seleccionar</option>
              <option v-for="pv in proveedores" :key="pv.id" :value="pv.id">{{ pv.nombre }}</option>
            </select>
          </div>
          <div class="flex items-center gap-4">
            <label class="flex items-center gap-1.5 text-sm text-gray-600"><input v-model="form.en_descuento" type="checkbox" class="rounded" /> En descuento</label>
            <label class="flex items-center gap-1.5 text-sm text-gray-600"><input v-model="form.es_perecedero" type="checkbox" class="rounded" /> Perecedero</label>
            <div v-if="form.es_perecedero"><label class="block text-sm font-medium text-gray-600">Vence</label><input v-model="form.fecha_vencimiento" type="date" class="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" /></div>
          </div>
          <div class="flex justify-end gap-2 pt-4 border-t">
            <button type="button" @click="closeModal" class="px-4 py-2 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-50 cursor-pointer">Cancelar</button>
            <button type="submit" class="px-4 py-2 text-sm rounded bg-red-700 text-white hover:bg-red-800 cursor-pointer border-none font-semibold">{{ editando ? 'Guardar Cambios' : 'Crear Producto' }}</button>
          </div>
        </form>
      </div>
        </div>
      </Transition>
    </Teleport>

    <!-- MODAL USUARIO -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="closeUserModal">
          <div class="modal-panel bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">{{ userEditando ? 'Editar' : 'Nuevo' }} Personal</h3>
        <form @submit.prevent="saveUser" class="space-y-3">
          <div><label class="block text-sm font-medium text-gray-600">Nombre *</label><input v-model="userForm.name" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" /></div>
          <div><label class="block text-sm font-medium text-gray-600">Email *</label><input v-model="userForm.email" type="email" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" /></div>
          <div><label class="block text-sm font-medium text-gray-600">{{ userEditando ? 'Nueva contraseña (dejar vacío para mantener)' : 'Contraseña *' }}</label><input v-model="userForm.password" type="password" minlength="8" :required="!userEditando" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" /></div>
          <div><label class="block text-sm font-medium text-gray-600">Rol *</label>
            <select v-model="userForm.role" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
              <option v-for="r in staffRoles" :key="r" :value="r">{{ r }}</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="block text-sm font-medium text-gray-600">Teléfono</label><input v-model="userForm.telefono" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" /></div>
            <div><label class="block text-sm font-medium text-gray-600">Dirección</label><input v-model="userForm.direccion" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" /></div>
          </div>
          <div class="flex justify-end gap-2 pt-4 border-t">
            <button type="button" @click="closeUserModal" class="px-4 py-2 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-50 cursor-pointer">Cancelar</button>
            <button type="submit" class="px-4 py-2 text-sm rounded bg-red-700 text-white hover:bg-red-800 cursor-pointer border-none font-semibold">{{ userEditando ? 'Guardar Cambios' : 'Crear Personal' }}</button>
          </div>
        </form>
      </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
