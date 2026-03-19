import { useState } from 'react'
import { Table, Button, Modal, Form, Input, Select, Space, Popconfirm, message, Tag } from 'antd'
import { PlusOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '../services/api'

interface Mission {
  id: string
  type: string
  adresse: string
  statut: string
  creneau_debut: string
  creneau_fin: string
  commande: { numero_commande: string }
  livreur: { nom: string; prenom: string }
}

const statutColors: Record<string, string> = {
  PLANIFIEE: 'blue',
  EN_COURS: 'orange',
  TERMINEE: 'green',
  ECHOUEE: 'red',
}

export default function Livraisons() {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isStatutModalOpen, setIsStatutModalOpen] = useState(false)
  const [selectedMission, setSelectedMission] = useState<Mission | null>(null)
  const [form] = Form.useForm()
  const [statutForm] = Form.useForm()
  const queryClient = useQueryClient()

  const { data, isLoading } = useQuery({
    queryKey: ['missions'],
    queryFn: () => api.get('/missions').then(res => res.data)
  })

  const { data: commandes } = useQuery({
    queryKey: ['commandes'],
    queryFn: () => api.get('/commandes').then(res => res.data)
  })

  const { data: employes } = useQuery({
    queryKey: ['employes'],
    queryFn: () => api.get('/employes').then(res => res.data)
  })

  const createMutation = useMutation({
    mutationFn: (values: Mission) => api.post('/missions', values),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['missions'] })
      message.success('Mission créée avec succès !')
      setIsModalOpen(false)
      form.resetFields()
    },
    onError: () => message.error('Erreur lors de la création.')
  })

  const changerStatutMutation = useMutation({
    mutationFn: (values: { statut: string }) =>
      api.patch(`/missions/${selectedMission?.id}/statut`, values),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['missions'] })
      message.success('Statut mis à jour !')
      setIsStatutModalOpen(false)
    },
    onError: () => message.error('Erreur lors du changement de statut.')
  })

  const deleteMutation = useMutation({
    mutationFn: (id: string) => api.delete(`/missions/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['missions'] })
      message.success('Mission supprimée avec succès !')
    },
    onError: () => message.error('Erreur lors de la suppression.')
  })

  const columns = [
    {
      title: 'Commande', key: 'commande',
      render: (_: unknown, record: Mission) => record.commande?.numero_commande
    },
    {
      title: 'Livreur', key: 'livreur',
      render: (_: unknown, record: Mission) =>
        `${record.livreur?.nom} ${record.livreur?.prenom}`
    },
    { title: 'Type', dataIndex: 'type', key: 'type' },
    { title: 'Adresse', dataIndex: 'adresse', key: 'adresse' },
    {
      title: 'Créneau', key: 'creneau',
      render: (_: unknown, record: Mission) =>
        `${record.creneau_debut?.slice(0, 16)} → ${record.creneau_fin?.slice(0, 16)}`
    },
    {
      title: 'Statut', dataIndex: 'statut', key: 'statut',
      render: (statut: string) => (
        <Tag color={statutColors[statut]}>{statut}</Tag>
      )
    },
    {
      title: 'Actions', key: 'actions',
      render: (_: unknown, record: Mission) => (
        <Space>
          <Button
            icon={<EditOutlined />}
            onClick={() => {
              setSelectedMission(record)
              statutForm.setFieldsValue({ statut: record.statut })
              setIsStatutModalOpen(true)
            }}
          >
            Statut
          </Button>
          <Popconfirm
            title="Supprimer cette mission ?"
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
        <h2>Livraisons et Collectes</h2>
        <Button type="primary" icon={<PlusOutlined />} onClick={() => setIsModalOpen(true)}>
          Nouvelle Mission
        </Button>
      </div>

      <Table
        columns={columns}
        dataSource={data?.data}
        rowKey="id"
        loading={isLoading}
      />

      <Modal
        title="Nouvelle Mission"
        open={isModalOpen}
        onCancel={() => setIsModalOpen(false)}
        onOk={() => form.submit()}
        confirmLoading={createMutation.isPending}
        width={600}
      >
        <Form form={form} layout="vertical" onFinish={(values) => createMutation.mutate(values)}>
          <Form.Item label="Commande" name="commande_id" rules={[{ required: true }]}>
            <Select
              options={commandes?.data?.map((c: { id: string; numero_commande: string }) => ({
                value: c.id,
                label: c.numero_commande
              }))}
            />
          </Form.Item>
          <Form.Item label="Livreur" name="livreur_id" rules={[{ required: true }]}>
            <Select
              options={employes?.data?.map((e: { id: string; nom: string; prenom: string }) => ({
                value: e.id,
                label: `${e.nom} ${e.prenom}`
              }))}
            />
          </Form.Item>
          <Form.Item label="Type" name="type" rules={[{ required: true }]}>
            <Select
              options={[
                { value: 'COLLECTE', label: 'Collecte' },
                { value: 'LIVRAISON', label: 'Livraison' },
              ]}
            />
          </Form.Item>
          <Form.Item label="Adresse" name="adresse" rules={[{ required: true }]}>
            <Input />
          </Form.Item>
          <Form.Item label="Créneau début" name="creneau_debut">
            <Input type="datetime-local" />
          </Form.Item>
          <Form.Item label="Créneau fin" name="creneau_fin">
            <Input type="datetime-local" />
          </Form.Item>
          <Form.Item label="Commentaire" name="commentaire">
            <Input.TextArea />
          </Form.Item>
        </Form>
      </Modal>

      <Modal
        title={`Changer statut — ${selectedMission?.commande?.numero_commande}`}
        open={isStatutModalOpen}
        onCancel={() => setIsStatutModalOpen(false)}
        onOk={() => statutForm.submit()}
        confirmLoading={changerStatutMutation.isPending}
      >
        <Form form={statutForm} layout="vertical" onFinish={(values) => changerStatutMutation.mutate(values)}>
          <Form.Item label="Nouveau statut" name="statut" rules={[{ required: true }]}>
            <Select
              options={[
                { value: 'PLANIFIEE', label: 'Planifiée' },
                { value: 'EN_COURS', label: 'En cours' },
                { value: 'TERMINEE', label: 'Terminée' },
                { value: 'ECHOUEE', label: 'Échouée' },
              ]}
            />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}