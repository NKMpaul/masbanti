import { useState } from 'react'
import { Table, Button, Modal, Form, Input, Select, Space, Popconfirm, message, Tag } from 'antd'
import { PlusOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '../services/api'

interface Employe {
  id: string
  nom: string
  prenom: string
  cin: string
  telephone: string
  email: string
  poste: string
  statut: string
  date_embauche: string
  agence_id: string
  agence: { nom: string }
  user: { email: string }
}

const statutColors: Record<string, string> = {
  ACTIF: 'green',
  INACTIF: 'red',
  EN_CONGE: 'orange',
}

export default function Employes() {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [editingEmploye, setEditingEmploye] = useState<Employe | null>(null)
  const [form] = Form.useForm()
  const queryClient = useQueryClient()

  const { data, isLoading } = useQuery({
    queryKey: ['employes'],
    queryFn: () => api.get('/employes').then(res => res.data)
  })

  const { data: agences } = useQuery({
    queryKey: ['agences'],
    queryFn: () => api.get('/agences').then(res => res.data)
  })

  const createMutation = useMutation({
    mutationFn: (values: Employe) => api.post('/employes', values),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['employes'] })
      message.success('Employé créé avec succès !')
      setIsModalOpen(false)
      form.resetFields()
    },
    onError: () => message.error('Erreur lors de la création.')
  })

  const updateMutation = useMutation({
    mutationFn: (values: Employe) => api.put(`/employes/${editingEmploye?.id}`, values),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['employes'] })
      message.success('Employé modifié avec succès !')
      setIsModalOpen(false)
      form.resetFields()
      setEditingEmploye(null)
    },
    onError: () => message.error('Erreur lors de la modification.')
  })

  const deleteMutation = useMutation({
    mutationFn: (id: string) => api.delete(`/employes/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['employes'] })
      message.success('Employé supprimé avec succès !')
    },
    onError: () => message.error('Erreur lors de la suppression.')
  })

  const handleEdit = (employe: Employe) => {
    setEditingEmploye(employe)
    form.setFieldsValue(employe)
    setIsModalOpen(true)
  }

  const handleAdd = () => {
    setEditingEmploye(null)
    form.resetFields()
    setIsModalOpen(true)
  }

  const columns = [
    { title: 'Nom', dataIndex: 'nom', key: 'nom' },
    { title: 'Prénom', dataIndex: 'prenom', key: 'prenom' },
    { title: 'CIN', dataIndex: 'cin', key: 'cin' },
    { title: 'Téléphone', dataIndex: 'telephone', key: 'telephone' },
    { title: 'Poste', dataIndex: 'poste', key: 'poste' },
    {
      title: 'Agence', key: 'agence',
      render: (_: unknown, record: Employe) => record.agence?.nom
    },
    {
      title: 'Statut', dataIndex: 'statut', key: 'statut',
      render: (statut: string) => (
        <Tag color={statutColors[statut]}>{statut}</Tag>
      )
    },
    {
      title: 'Actions', key: 'actions',
      render: (_: unknown, record: Employe) => (
        <Space>
          <Button icon={<EditOutlined />} onClick={() => handleEdit(record)} />
          <Popconfirm
            title="Supprimer cet employé ?"
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
        <h2>Gestion des Employés</h2>
        <Button type="primary" icon={<PlusOutlined />} onClick={handleAdd}>
          Nouvel Employé
        </Button>
      </div>

      <Table
        columns={columns}
        dataSource={data?.data}
        rowKey="id"
        loading={isLoading}
      />

      <Modal
        title={editingEmploye ? 'Modifier Employé' : 'Nouvel Employé'}
        open={isModalOpen}
        onCancel={() => setIsModalOpen(false)}
        onOk={() => form.submit()}
        confirmLoading={createMutation.isPending || updateMutation.isPending}
        width={600}
      >
        <Form form={form} layout="vertical" onFinish={(values) => editingEmploye ? updateMutation.mutate(values) : createMutation.mutate(values)}>
          <Form.Item label="Nom" name="nom" rules={[{ required: true }]}>
            <Input />
          </Form.Item>
          <Form.Item label="Prénom" name="prenom" rules={[{ required: true }]}>
            <Input />
          </Form.Item>
          <Form.Item label="CIN" name="cin" rules={[{ required: true }]}>
            <Input />
          </Form.Item>
          <Form.Item label="Téléphone" name="telephone">
            <Input />
          </Form.Item>
          {!editingEmploye && (
            <div>
              <Form.Item label="Email" name="email" rules={[{ required: true, type: 'email' }]}>
                <Input />
              </Form.Item>
              <Form.Item label="Mot de passe" name="password" rules={[{ required: true, min: 8 }]}>
                <Input.Password />
              </Form.Item>
            </div>
          )}
          <Form.Item label="Poste" name="poste" rules={[{ required: true }]}>
            <Select
              options={[
                { value: 'RECEPTIONNISTE', label: 'Réceptionniste' },
                { value: 'OPERATEUR_NETTOYAGE', label: 'Opérateur nettoyage' },
                { value: 'REPASSEUR', label: 'Repasseur' },
                { value: 'LIVREUR', label: 'Livreur' },
                { value: 'GERANT', label: 'Gérant' },
              ]}
            />
          </Form.Item>
          <Form.Item label="Agence" name="agence_id" rules={[{ required: true }]}>
            <Select
              options={agences?.data?.map((a: { id: string; nom: string }) => ({
                value: a.id,
                label: a.nom
              }))}
            />
          </Form.Item>
          <Form.Item label="Date d'embauche" name="date_embauche" rules={[{ required: true }]}>
            <Input type="date" />
          </Form.Item>
          {editingEmploye && (
            <Form.Item label="Statut" name="statut">
              <Select
                options={[
                  { value: 'ACTIF', label: 'Actif' },
                  { value: 'INACTIF', label: 'Inactif' },
                  { value: 'EN_CONGE', label: 'En congé' },
                ]}
              />
            </Form.Item>
          )}
        </Form>
      </Modal>
    </div>
  )
}