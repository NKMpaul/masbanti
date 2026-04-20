import { Table, Button, Badge, Space, Tag, message } from 'antd'
import { CheckOutlined, DeleteOutlined } from '@ant-design/icons'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useState } from 'react'
import dayjs from 'dayjs'
import api from '../services/api'

interface Notification {
  id: string
  titre: string
  message: string
  type: string
  canal: string
  lu: boolean
  created_at: string
}

export default function Notifications() {
  const queryClient = useQueryClient()
  const [page, setPage] = useState(1) // État pour la pagination Laravel

  // 1. Récupération des notifications paginées
  const { data, isLoading } = useQuery({
    queryKey: ['notifications', page],
    queryFn: () => api.get(`/notifications?page=${page}`).then(res => res.data),
  })

  // 2. Récupération du compteur (Badge)
  const { data: nonLues } = useQuery({
    queryKey: ['notifications-non-lues'],
    queryFn: () => api.get('/notifications/non-lues').then(res => res.data),
  })

  const marquerLuMutation = useMutation({
    mutationFn: (id: string) => api.patch(`/notifications/${id}/lu`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['notifications'] })
      queryClient.invalidateQueries({ queryKey: ['notifications-non-lues'] })
    }
  })

  const marquerToutLuMutation = useMutation({
    mutationFn: () => api.patch('/notifications/tout-lu'),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['notifications'] })
      queryClient.invalidateQueries({ queryKey: ['notifications-non-lues'] })
      message.success('Toutes les notifications marquées comme lues !')
    }
  })

  const deleteMutation = useMutation({
    mutationFn: (id: string) => api.delete(`/notifications/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['notifications'] })
      queryClient.invalidateQueries({ queryKey: ['notifications-non-lues'] })
      message.success('Notification supprimée !')
    }
  })

  const columns = [
    {
      title: 'Titre', 
      dataIndex: 'titre', 
      key: 'titre',
      render: (titre: string, record: Notification) => (
        <span style={{ fontWeight: record.lu ? 'normal' : 'bold' }}>
          {!record.lu && <Badge status="processing" style={{ marginRight: 8 }} />}
          {titre}
        </span>
      )
    },
    { title: 'Message', dataIndex: 'message', key: 'message' },
    {
      title: 'Type', 
      dataIndex: 'type', 
      key: 'type',
      render: (type: string) => <Tag color="blue">{type}</Tag>
    },
    {
      title: 'Canal', 
      dataIndex: 'canal', 
      key: 'canal',
      render: (canal: string) => (
        <Tag color={canal === 'EMAIL' ? 'green' : canal === 'PUSH' ? 'orange' : 'default'}>
          {canal}
        </Tag>
      )
    },
    {
      title: 'Statut', 
      dataIndex: 'lu', 
      key: 'lu',
      render: (lu: boolean) => (
        <Tag color={lu ? 'default' : 'blue'}>{lu ? 'Lu' : 'Non lu'}</Tag>
      )
    },
    {
      title: 'Date', 
      dataIndex: 'created_at', 
      key: 'created_at',
      render: (date: string) => dayjs(date).format('DD/MM/YYYY HH:mm') // Date lisible à la française
    },
    {
      title: 'Actions', 
      key: 'actions',
      render: (_: unknown, record: Notification) => (
        <Space>
          {!record.lu && (
            <Button
              size="small"
              icon={<CheckOutlined />}
              onClick={() => marquerLuMutation.mutate(record.id)}
              loading={marquerLuMutation.isPending}
            >
              Lue
            </Button>
          )}
          <Button
            size="small"
            danger
            icon={<DeleteOutlined />}
            onClick={() => deleteMutation.mutate(record.id)}
            loading={deleteMutation.isPending}
          />
        </Space>
      )
    }
  ]

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 16 }}>
        <h2>
          Notifications
          {nonLues?.count > 0 && (
            <Badge count={nonLues.count} style={{ marginLeft: 8 }} />
          )}
        </h2>
        <Button
          onClick={() => marquerToutLuMutation.mutate()}
          disabled={!nonLues || nonLues.count === 0}
          loading={marquerToutLuMutation.isPending}
        >
          Tout marquer comme lu
        </Button>
      </div>

      <Table
        columns={columns}
        dataSource={data?.data} // Les données réelles renvoyées par Laravel paginate()
        rowKey="id"
        loading={isLoading}
        rowClassName={(record: Notification) => record.lu ? '' : 'ant-table-row-selected'}
        pagination={{
          current: page,
          pageSize: data?.per_page || 20,
          total: data?.total || 0,
          onChange: (newPage) => setPage(newPage), // Gère le clic sur les pages (1, 2, 3...)
          showSizeChanger: false
        }}
      />
    </div>
  )
}