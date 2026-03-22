import { useState } from 'react'
import { Table, Button, Modal, Form, Input, Select, Space, Tag, message } from 'antd'
import { PlusOutlined, EyeOutlined } from '@ant-design/icons'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '../services/api'
import { useEffect } from 'react'

interface Commande {
  id: string
  numero_commande: string
  statut: string
  mode_livraison: string
  montant_total: number
  montant_paye: number
  date_depot: string
  date_retrait_prevue: string
  client: { nom: string; prenom: string }
  agence: { nom: string }
}

const statutColors: Record<string, string> = {
  CREEE: 'blue',
  EN_ATTENTE: 'orange',
  COLLECTEE: 'cyan',
  RECUE_AGENCE: 'purple',
  EN_TRAITEMENT: 'gold',
  PRETE: 'green',
  EN_LIVRAISON: 'geekblue',
  LIVREE: 'success',
  RETIREE_AGENCE: 'success',
  ANNULEE: 'red',
}

export default function Commandes() {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isStatutModalOpen, setIsStatutModalOpen] = useState(false)
  const [selectedCommande, setSelectedCommande] = useState<Commande | null>(null)
  const [form] = Form.useForm()
  const [statutForm] = Form.useForm()
  const queryClient = useQueryClient()

  useEffect(() => {
    const channel = window.Echo.channel('commandes')
    
    channel.listen('.statut.change', () => {
      queryClient.invalidateQueries({ queryKey: ['commandes'] })
    })

    return () => {
      window.Echo.leaveChannel('commandes')
    }
  }, [queryClient])
  const { data, isLoading } = useQuery({
    queryKey: ['commandes'],
    queryFn: () => api.get('/commandes').then(res => res.data)
  })

  const { data: clients } = useQuery({
    queryKey: ['clients'],
    queryFn: () => api.get('/clients').then(res => res.data)
  })

  const { data: agences } = useQuery({
    queryKey: ['agences'],
    queryFn: () => api.get('/agences').then(res => res.data)
  })

  const { data: typeArticles } = useQuery({
    queryKey: ['type-articles'],
    queryFn: () => api.get('/type-articles').then(res => res.data)
  })
  

  const createMutation = useMutation({
    mutationFn: (values: Commande) => api.post('/commandes', values),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['commandes'] })
      message.success('Commande créée avec succès !')
      setIsModalOpen(false)
      form.resetFields()
    },
    onError: () => message.error('Erreur lors de la création.')
  })

  const changerStatutMutation = useMutation({
    mutationFn: (values: { statut: string }) =>
      api.patch(`/commandes/${selectedCommande?.id}/statut`, values),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['commandes'] })
      message.success('Statut mis à jour !')
      setIsStatutModalOpen(false)
    },
    onError: () => message.error('Erreur lors du changement de statut.')
  })

  
  const columns = [
    { title: 'Numéro', dataIndex: 'numero_commande', key: 'numero_commande' },
    {
      title: 'Client', key: 'client',
      render: (_: unknown, record: Commande) =>
        `${record.client?.nom} ${record.client?.prenom}`
    },
    {
      title: 'Agence', key: 'agence',
      render: (_: unknown, record: Commande) => record.agence?.nom
    },
    {
      title: 'Statut', dataIndex: 'statut', key: 'statut',
      render: (statut: string) => (
        <Tag color={statutColors[statut]}>{statut}</Tag>
      )
    },
    { title: 'Mode', dataIndex: 'mode_livraison', key: 'mode_livraison' },
    {
      title: 'Montant', dataIndex: 'montant_total', key: 'montant_total',
      render: (montant: number) => `${montant} DH`
    },
    {
      title: 'Actions', key: 'actions',
      render: (_: unknown, record: Commande) => (
        <Space>
          <Button
            icon={<EyeOutlined />}
            onClick={() => {
              setSelectedCommande(record)
              statutForm.setFieldsValue({ statut: record.statut })
              setIsStatutModalOpen(true)
            }}
          >
            Statut
          </Button>
        </Space>
      )
    }
  ]

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 16 }}>
        <h2>Gestion des Commandes</h2>
        <Button type="primary" icon={<PlusOutlined />} onClick={() => setIsModalOpen(true)}>
          Nouvelle Commande
        </Button>
      </div>

      <Table
        columns={columns}
        dataSource={data?.data}
        rowKey="id"
        loading={isLoading}
      />

      <Modal
        title="Nouvelle Commande"
        open={isModalOpen}
        onCancel={() => setIsModalOpen(false)}
        onOk={() => form.submit()}
        confirmLoading={createMutation.isPending}
        width={700}
      >
        <Form form={form} layout="vertical" onFinish={(values) => createMutation.mutate(values)}>
          <Form.Item label="Client" name="client_id" rules={[{ required: true }]}>
            <Select
              options={clients?.data?.map((c: { id: string; nom: string; prenom: string }) => ({
                value: c.id,
                label: `${c.nom} ${c.prenom}`
              }))}
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
          <Form.Item label="Date dépôt" name="date_depot" rules={[{ required: true }]}>
            <Input type="datetime-local" />
          </Form.Item>
          <Form.Item label="Date retrait prévue" name="date_retrait_prevue">
            <Input type="datetime-local" />
          </Form.Item>
          <Form.Item label="Mode livraison" name="mode_livraison" rules={[{ required: true }]}>
            <Select
              options={[
                { value: 'EN_AGENCE', label: 'En agence' },
                { value: 'LIVRAISON_DOMICILE', label: 'Livraison domicile' },
              ]}
            />
          </Form.Item>
          <Form.Item label="Article" name={['articles', 0, 'type_article_id']} rules={[{ required: true }]}>
            <Select
              options={typeArticles?.map((t: { id: string; nom: string }) => ({
                value: t.id,
                label: t.nom
              }))}
            />
          </Form.Item>
          <Form.Item label="Quantité" name={['articles', 0, 'quantite']} rules={[{ required: true }]}>
            <Input type="number" min={1} />
          </Form.Item>
          <Form.Item label="Prix unitaire (DH)" name={['articles', 0, 'prix_unitaire']} rules={[{ required: true }]}>
            <Input type="number" min={0} />
          </Form.Item>
          <Form.Item label="Commentaire" name="commentaire">
            <Input.TextArea />
          </Form.Item>
        </Form>
      </Modal>

      <Modal
        title={`Changer statut — ${selectedCommande?.numero_commande}`}
        open={isStatutModalOpen}
        onCancel={() => setIsStatutModalOpen(false)}
        onOk={() => statutForm.submit()}
        confirmLoading={changerStatutMutation.isPending}
      >
        <Form form={statutForm} layout="vertical" onFinish={(values) => changerStatutMutation.mutate(values)}>
          <Form.Item label="Nouveau statut" name="statut" rules={[{ required: true }]}>
            <Select
              options={[
                { value: 'CREEE', label: 'Créée' },
                { value: 'EN_ATTENTE', label: 'En attente' },
                { value: 'COLLECTEE', label: 'Collectée' },
                { value: 'RECUE_AGENCE', label: 'Reçue agence' },
                { value: 'EN_TRAITEMENT', label: 'En traitement' },
                { value: 'PRETE', label: 'Prête' },
                { value: 'EN_LIVRAISON', label: 'En livraison' },
                { value: 'LIVREE', label: 'Livrée' },
                { value: 'RETIREE_AGENCE', label: 'Retirée agence' },
                { value: 'ANNULEE', label: 'Annulée' },
              ]}
            />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}