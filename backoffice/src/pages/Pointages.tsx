import { useState } from 'react'
import { Table, Button, Modal, Form, Input, Select, Space, Popconfirm, message, Tag } from 'antd'
import { PlusOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '../services/api'

interface Pointage {
  id: string
  employe_id: string
  date_pointage: string
  heure_arrivee: string
  heure_depart: string
  statut: string
  employe: { nom: string; prenom: string }
}

const statutColors: Record<string, string> = {
  PRESENT: 'green',
  ABSENT: 'red',
  EN_RETARD: 'orange',
  CONGE: 'blue',
}

export default function Pointages() {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [editingPointage, setEditingPointage] = useState<Pointage | null>(null)
  const [form] = Form.useForm()
  const queryClient = useQueryClient()

  const { data, isLoading } = useQuery({
    queryKey: ['pointages'],
    queryFn: () => api.get('/pointages').then(res => res.data)
  })

  const { data: employes } = useQuery({
    queryKey: ['employes'],
    queryFn: () => api.get('/employes').then(res => res.data)
  })

  const createMutation = useMutation({
    mutationFn: (values: Pointage) => api.post('/pointages', values),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['pointages'] })
      message.success('Pointage enregistré avec succès !')
      setIsModalOpen(false)
      form.resetFields()
    },
    onError: () => message.error('Erreur lors de l\'enregistrement.')
  })

  const updateMutation = useMutation({
    mutationFn: (values: Pointage) => api.put(`/pointages/${editingPointage?.id}`, values),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['pointages'] })
      message.success('Pointage modifié avec succès !')
      setIsModalOpen(false)
      form.resetFields()
      setEditingPointage(null)
    },
    onError: () => message.error('Erreur lors de la modification.')
  })

  const deleteMutation = useMutation({
    mutationFn: (id: string) => api.delete(`/pointages/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['pointages'] })
      message.success('Pointage supprimé avec succès !')
    },
    onError: () => message.error('Erreur lors de la suppression.')
  })

  const handleEdit = (pointage: Pointage) => {
    setEditingPointage(pointage)
    form.setFieldsValue(pointage)
    setIsModalOpen(true)
  }

  const handleAdd = () => {
    setEditingPointage(null)
    form.resetFields()
    setIsModalOpen(true)
  }

  const columns = [
    {
      title: 'Employé', key: 'employe',
      render: (_: unknown, record: Pointage) =>
        `${record.employe?.nom} ${record.employe?.prenom}`
    },
    { title: 'Date', dataIndex: 'date_pointage', key: 'date_pointage' },
    { title: 'Arrivée', dataIndex: 'heure_arrivee', key: 'heure_arrivee' },
    { title: 'Départ', dataIndex: 'heure_depart', key: 'heure_depart' },
    {
      title: 'Statut', dataIndex: 'statut', key: 'statut',
      render: (statut: string) => (
        <Tag color={statutColors[statut]}>{statut}</Tag>
      )
    },
    {
      title: 'Actions', key: 'actions',
      render: (_: unknown, record: Pointage) => (
        <Space>
          <Button icon={<EditOutlined />} onClick={() => handleEdit(record)} />
          <Popconfirm
            title="Supprimer ce pointage ?"
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
        <h2>Pointage des Employés</h2>
        <Button type="primary" icon={<PlusOutlined />} onClick={handleAdd}>
          Nouveau Pointage
        </Button>
      </div>

      <Table
        columns={columns}
        dataSource={data?.data}
        rowKey="id"
        loading={isLoading}
      />

      <Modal
        title={editingPointage ? 'Modifier Pointage' : 'Nouveau Pointage'}
        open={isModalOpen}
        onCancel={() => setIsModalOpen(false)}
        onOk={() => form.submit()}
        confirmLoading={createMutation.isPending || updateMutation.isPending}
      >
        <Form form={form} layout="vertical" onFinish={(values) => editingPointage ? updateMutation.mutate(values) : createMutation.mutate(values)}>
          <Form.Item label="Employé" name="employe_id" rules={[{ required: true }]}>
            <Select
              options={employes?.data?.map((e: { id: string; nom: string; prenom: string }) => ({
                value: e.id,
                label: `${e.nom} ${e.prenom}`
              }))}
            />
          </Form.Item>
          <Form.Item label="Date" name="date_pointage" rules={[{ required: true }]}>
            <Input type="date" />
          </Form.Item>
          <Form.Item label="Heure arrivée" name="heure_arrivee">
            <Input type="time" />
          </Form.Item>
          <Form.Item label="Heure départ" name="heure_depart">
            <Input type="time" />
          </Form.Item>
          <Form.Item label="Statut" name="statut" rules={[{ required: true }]}>
            <Select
              options={[
                { value: 'PRESENT', label: 'Présent' },
                { value: 'ABSENT', label: 'Absent' },
                { value: 'EN_RETARD', label: 'En retard' },
                { value: 'CONGE', label: 'Congé' },
              ]}
            />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}