export const admin = [
  { path: '/hall/list', redirect: '/admin' },
  { path: '/geoobject/list', redirect: '/admin' },
  { path: '/route/list', redirect: '/admin' },

  {
    path: '/admin',
    meta: { requiresAuth: true },
    component: () => import('@/views/Admin'),

    children: [
      {
        path: 'geo-list',
        meta: { requiresAuth: true },
        component: () => import('@/components/admin/entity/geoobject/ListPage')
      },

      {
        path: 'geo-add',
        meta: { requiresAuth: true },
        component: () => import('@/components/admin/entity/geoobject/AddGeo')
      },

      {
        path: 'geo-update',
        name: 'updateGeo',
        meta: { requiresAuth: true },
        component: () =>
          import('@/components/admin/entity/geoobject/UpdateGeo')
      },

      {
        path: 'hall-list',
        meta: { requiresAuth: true },
        component: () => import('@/components/admin/entity/hall/ListPage')
      },

      {
        path: 'hall-add',
        meta: { requiresAuth: true },
        component: () => import('@/components/admin/entity/hall/AddHall')
      },

      {
        path: 'hall-update',
        name: 'updateHall',
        meta: { requiresAuth: true },
        component: () => import('@/components/admin/entity/hall/UpdateHall')
      },

      {
        path: 'route-list',
        meta: { requiresAuth: true },
        component: () => import('@/components/admin/entity/route/ListPage')
      },

      {
        path: 'route-add',
        meta: { requiresAuth: true },
        component: () => import('@/components/admin/entity/route/AddRoute')
      },

      {
        path: 'route-update',
        name: 'updateRoute',
        meta: { requiresAuth: true },
        component: () => import('@/components/admin/entity/route/UpdateRoute')
      }
    ]
  }
]
