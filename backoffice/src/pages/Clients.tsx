import { useState } from 'react'
import { Table, Button, Modal, Form, Input, Select, Space, Popconfirm, message, Tag } from 'antd'
import { PlusOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '../services/api'

interface Client {
  id: string
  nom: string
  prenom: string
  telephone: string
  adresse: string
  ville: string
  points_fidelite: number
  niveau_fidelite: string
  user: { email: string }
}

export default function Clients() {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [editingClient, setEditingClient] = useState<Client | null>(null)
  const [form] = Form.useForm()
  const queryClient = useQueryClient()

  const { data, isLoading } = useQuery({
    queryKey: ['clients'],
    queryFn: () => api.get('/clients').then(res => res.data)
  })

  const createMutation = useMutation({
    mutationFn: (values: Client) => api.post('/clients', values),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['clients'] })
      message.success('Client créé avec succès !')
      setIsModalOpen(false)
      form.resetFields()
    },
    onError: () => message.error('Erreur lors de la création.')
  })

  const updateMutation = useMutation({
    mutationFn: (values: Client) => api.put(`/clients/${editingClient?.id}`, values),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['clients'] })
      message.success('Client modifié avec succès !')
      setIsModalOpen(false)
      form.resetFields()
      setEditingClient(null)
    },
    onError: () => message.error('Erreur lors de la modification.')
  })

  const deleteMutation = useMutation({
    mutationFn: (id: string) => api.delete(`/clients/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['clients'] })
      message.success('Client supprimé avec succès !')
    },
    onError: () => message.error('Erreur lors de la suppression.')
  })

  const handleSubmit = (values: Client) => {
    if (editingClient) {
      updateMutation.mutate(values)
    } else {
      createMutation.mutate(values)
    }
  }

  const handleEdit = (client: Client) => {
    setEditingClient(client)
    form.setFieldsValue(client)
    setIsModalOpen(true)
  }

  const handleAdd = () => {
    setEditingClient(null)
    form.resetFields()
    setIsModalOpen(true)
  }

  const niveauColors: Record<string, string> = {
    BRONZE: 'orange',
    SILVER: 'default',
    GOLD: 'gold',
    PLATINUM: 'blue',
  }

  const columns = [
    { title: 'Nom', dataIndex: 'nom', key: 'nom' },
    { title: 'Prénom', dataIndex: 'prenom', key: 'prenom' },
    { title: 'Téléphone', dataIndex: 'telephone', key: 'telephone' },
    { title: 'Ville', dataIndex: 'ville', key: 'ville' },
    {
      title: 'Email', key: 'email',
      render: (_: unknown, record: Client) => record.user?.email
    },
    { title: 'Points', dataIndex: 'points_fidelite', key: 'points_fidelite' },
    {
      title: 'Niveau', dataIndex: 'niveau_fidelite', key: 'niveau_fidelite',
      render: (niveau: string) => (
        <Tag color={niveauColors[niveau]}>{niveau}</Tag>
      )
    },
    {
      title: 'Actions', key: 'actions',
      render: (_: unknown, record: Client) => (
        <Space>
          <Button icon={<EditOutlined />} onClick={() => handleEdit(record)} />
          <Popconfirm
            title="Supprimer ce client ?"
            onConfirm={() => deleteMutation.mutate(record.id)}
            okText="Oui"
            cancelText="Non"
          >
            <Button icon={<DeleteOutlined />} danger />
          </Popconfirm>
        </Space>
      )
    }
  ]

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 16 }}>
        <h2>Gestion des Clients</h2>
        <Button type="primary" icon={<PlusOutlined />} onClick={handleAdd}>
          Nouveau Client
        </Button>
      </div>
      <Table
        columns={columns}
        dataSource={data?.data}
        rowKey="id"
        loading={isLoading}
      />
      <Modal
        title={editingClient ? 'Modifier Client' : 'Nouveau Client'}
        open={isModalOpen}
        onCancel={() => setIsModalOpen(false)}
        onOk={() => form.submit()}
        confirmLoading={createMutation.isPending || updateMutation.isPending}
      >
        <Form form={form} layout="vertical" onFinish={handleSubmit}>
          <Form.Item label="Nom" name="nom" rules={[{ required: true }]}>
            <Input />
          </Form.Item>
          <Form.Item label="Prénom" name="prenom" rules={[{ required: true }]}>
            <Input />
          </Form.Item>
          {!editingClient && (
            <div>
              <Form.Item label="Email" name="email" rules={[{ required: true, type: 'email' }]}>
                <Input />
              </Form.Item>
              <Form.Item label="Mot de passe" name="password" rules={[{ required: true, min: 8 }]}>
                <Input.Password />
              </Form.Item>
            </div>
          )}
          <Form.Item label="Téléphone" name="telephone">
            <Input />
          </Form.Item>
          <Form.Item label="Adresse" name="adresse">
            <Input />
          </Form.Item>
          <Form.Item label="Ville" name="ville">
            <Input />
          </Form.Item>
          {editingClient && (
            <Form.Item label="Niveau fidélité" name="niveau_fidelite">
              <Select
                options={[
                  { value: 'BRONZE', label: 'Bronze' },
                  { value: 'SILVER', label: 'Silver' },
                  { value: 'GOLD', label: 'Gold' },
                  { value: 'PLATINUM', label: 'Platinum' },
                ]}
              />
            </Form.Item>
          )}
        </Form>
      </Modal>
    </div>
  )
}