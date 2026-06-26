import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'home', component: () => import('../views/HomeView.vue') },
    { path: '/tienda', name: 'catalogo', component: () => import('../views/CatalogoView.vue') },
    { path: '/producto/:id', name: 'detalle', component: () => import('../views/DetalleProductoView.vue') },
    { path: '/carrito', name: 'carrito', component: () => import('../views/CarritoView.vue'), meta: { requiresAuth: true } },
    { path: '/picking', name: 'picking', component: () => import('../views/PickingView.vue'), meta: { requiresAuth: true, roles: ['Administrador', 'Encargado', 'Picking'] } },
    { path: '/repartidor', name: 'repartidor', component: () => import('../views/RepartidorView.vue'), meta: { requiresAuth: true, roles: ['Administrador', 'Repartidor'] } },
    { path: '/tracking', name: 'tracking', component: () => import('../views/TrackingView.vue'), meta: { requiresAuth: true } },
    { path: '/login', name: 'login', component: () => import('../views/LoginView.vue'), meta: { guest: true } },
    { path: '/register', name: 'register', component: () => import('../views/RegisterView.vue'), meta: { guest: true } },
    { path: '/olvide-password', name: 'olvide-password', component: () => import('../views/OlvidePasswordView.vue'), meta: { guest: true } },
    { path: '/reset-password', name: 'reset-password', component: () => import('../views/ResetPasswordView.vue'), meta: { guest: true } },
    { path: '/perfil', name: 'perfil', component: () => import('../views/PerfilView.vue'), meta: { requiresAuth: true } },
    { path: '/admin', name: 'admin', component: () => import('../views/AdminView.vue'), meta: { requiresAuth: true, roles: ['Administrador'] } },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('../views/NotFoundView.vue') },
  ]
})

router.beforeEach((to, from) => {
  const auth = useAuthStore()

  // Validate numeric ID for product detail route
  if (to.name === 'detalle' && to.params.id) {
    const id = Number(to.params.id)
    if (!Number.isInteger(id) || id < 1) {
      return { name: 'catalogo' }
    }
  }

  // Redirect authenticated users away from guest pages (login, register, etc.)
  if (to.meta.guest && auth.token) {
    return { name: 'home' }
  }

  // Require auth
  if (to.meta.requiresAuth && !auth.token) {
    return { name: 'login', query: { redirect: to.name as string } }
  }

  // Role check
  if (to.meta.roles && !auth.user?.roles?.some((r: any) => (to.meta.roles as string[]).includes(r.name))) {
    return { name: 'catalogo' }
  }
})

export default router
