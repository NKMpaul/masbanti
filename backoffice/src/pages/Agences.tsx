import { useState } from 'react'
import { Table, Button, Modal, Form, Input, Select, Space, Popconfirm, message } from 'antd'
import { PlusOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '../services/api'

interface Agence {
  id: string
  nom: string
  adresse: string
  ville: string
  telephone: string
  email: string
  horaire_ouverture: string
  statut: string
}

export default function Agences() {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [editingAgence, setEditingAgence] = useState<Agence | null>(null)
  const [form] = Form.useForm()
  const queryClient = useQueryClient()

  const { data, isLoading } = useQuery({
    queryKey: ['agences'],
    queryFn: () => api.get('/agences').then(res => res.data)
  })

  const createMutation = useMutation({
    mutationFn: (values: Agence) => api.post('/agences', values),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['agences'] })
      message.success('Agence créée avec succès !')
      setIsModalOpen(false)
      form.resetFields()
    },
    onError: () => message.error('Erreur lors de la création.')
  })

  const updateMutation = useMutation({
    mutationFn: (values: Agence) => api.put(`/agences/${editingAgence?.id}`, values),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['agences'] })
      message.success('Agence modifiée avec succès !')
      setIsModalOpen(false)
      form.resetFields()
      setEditingAgence(null)
    },
    onError: () => message.error('Erreur lors de la modification.')
  })

  const deleteMutation = useMutation({
    mutationFn: (id: string) => api.delete(`/agences/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['agences'] })
      message.success('Agence supprimée avec succès !')
    },
    onError: () => message.error('Erreur lors de la suppression.')
  })

  const handleSubmit = (values: Agence) => {
    if (editingAgence) {
      updateMutation.mutate(values)
    } else {
      createMutation.mutate(values)
    }
  }

  const handleEdit = (agence: Agence) => {
    setEditingAgence(agence)
    form.setFieldsValue(agence)
    setIsModalOpen(true)
  }

  const handleAdd = () => {
    setEditingAgence(null)
    form.resetFields()
    setIsModalOpen(true)
  }

  const columns = [
    { title: 'Nom', dataIndex: 'nom', key: 'nom' },
    { title: 'Ville', dataIndex: 'ville', key: 'ville' },
    { title: 'Téléphone', dataIndex: 'telephone', key: 'telephone' },
    { title: 'Email', dataIndex: 'email', key: 'email' },
    {
      title: 'Statut', dataIndex: 'statut', key: 'statut',
      render: (statut: string) => (
        <span style={{ color: statut === 'ACTIVE' ? 'green' : statut === 'INACTIVE' ? 'red' : 'orange' }}>
          {statut}
        </span>
      )
    },
    {
      title: 'Actions', key: 'actions',
      render: (_: unknown, record: Agence) => (
        <Space>
          <Button icon={<EditOutlined />} onClick={() => handleEdit(record)} />
          <Popconfirm
            title="Supprimer cette agence ?"
            onConfirm={() => deleteMutation.mutate(record.id)}
            okText="Oui" cancelText="Non"
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
        <h2>Gestion des Agences</h2>
        <Button type="primary" icon={<PlusOutlined />} onClick={handleAdd}>
          Nouvelle Agence
        </Button>
      </div>

      <Table
        columns={columns}
        dataSource={data?.data}
        rowKey="id"
        loading={isLoading}
      />

      <Modal
        title={editingAgence ? 'Modifier Agence' : 'Nouvelle Agence'}
        open={isModalOpen}
        onCancel={() => setIsModalOpen(false)}
        onOk={() => form.submit()}
        confirmLoading={createMutation.isPending || updateMutation.isPending}
      >
        <Form form={form} layout="vertical" onFinish={handleSubmit}>
          <Form.Item label="Nom" name="nom" rules={[{ required: true }]}>
            <Input />
          </Form.Item>
          <Form.Item label="Adresse" name="adresse" rules={[{ required: true }]}>
            <Input />
          </Form.Item>
          <Form.Item label="Ville" name="ville" rules={[{ required: true }]}>
            <Input />
          </Form.Item>
          <Form.Item label="Téléphone" name="telephone" rules={[{ required: true }]}>
            <Input />
          </Form.Item>
          <Form.Item label="Email" name="email" rules={[{ required: true, type: 'email' }]}>
            <Input />
          </Form.Item>
          <Form.Item label="Horaire d'ouverture" name="horaire_ouverture">
            <Input placeholder="Ex: 08h00 - 18h00" />
          </Form.Item>
            <Form.Item label="Statut" name="statut" initialValue="ACTIVE">
            <Select
                options={[
                { value: 'ACTIVE', label: 'Active' },
                { value: 'INACTIVE', label: 'Inactive' },
                { value: 'EN_MAINTENANCE', label: 'En maintenance' },
                ]}
            />
            </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}