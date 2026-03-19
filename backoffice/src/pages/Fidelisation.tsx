import { useState } from 'react'
import { Table, Button, Modal, Form, Input, Card, Row, Col, Tag, message, Statistic } from 'antd'
import { GiftOutlined } from '@ant-design/icons'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '../services/api'

interface Client {
  id: string
  nom: string
  prenom: string
  points_fidelite: number
  niveau_fidelite: string
  code_parrainage: string
}

const niveauColors: Record<string, string> = {
  BRONZE: 'orange',
  SILVER: 'default',
  GOLD: 'gold',
  PLATINUM: 'blue',
}

export default function Fidelisation() {
  const [isCrediterModalOpen, setIsCrediterModalOpen] = useState(false)
  const [isDebiterModalOpen, setIsDebiterModalOpen] = useState(false)
  const [selectedClient, setSelectedClient] = useState<Client | null>(null)
  const [crediterForm] = Form.useForm()
  const [debiterForm] = Form.useForm()
  const queryClient = useQueryClient()

  const { data, isLoading } = useQuery({
    queryKey: ['clients'],
    queryFn: () => api.get('/clients').then(res => res.data)
  })

  const crediterMutation = useMutation({
    mutationFn: (values: { points: number; motif: string }) =>
      api.post('/fidelite/crediter', { ...values, client_id: selectedClient?.id }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['clients'] })
      message.success('Points crédités avec succès !')
      setIsCrediterModalOpen(false)
      crediterForm.resetFields()
    },
    onError: () => message.error('Erreur lors du crédit.')
  })

  const debiterMutation = useMutation({
    mutationFn: (values: { points: number; motif: string }) =>
      api.post('/fidelite/debiter', { ...values, client_id: selectedClient?.id }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['clients'] })
      message.success('Points débités avec succès !')
      setIsDebiterModalOpen(false)
      debiterForm.resetFields()
    },
    onError: () => message.error('Points insuffisants ou erreur.')
  })

  const columns = [
    { title: 'Nom', dataIndex: 'nom', key: 'nom' },
    { title: 'Prénom', dataIndex: 'prenom', key: 'prenom' },
    {
      title: 'Points', dataIndex: 'points_fidelite', key: 'points_fidelite',
      render: (points: number) => <strong>{points} pts</strong>
    },
    {
      title: 'Niveau', dataIndex: 'niveau_fidelite', key: 'niveau_fidelite',
      render: (niveau: string) => (
        <Tag color={niveauColors[niveau]}>{niveau}</Tag>
      )
    },
    {
      title: 'Code parrainage', dataIndex: 'code_parrainage', key: 'code_parrainage',
      render: (code: string) => code ? <Tag color="purple">{code}</Tag> : '—'
    },
    {
      title: 'Actions', key: 'actions',
      render: (_: unknown, record: Client) => (
        <div style={{ display: 'flex', gap: 8 }}>
          <Button
            size="small"
            type="primary"
            onClick={() => { setSelectedClient(record); setIsCrediterModalOpen(true) }}
          >
            + Points
          </Button>
          <Button
            size="small"
            danger
            onClick={() => { setSelectedClient(record); setIsDebiterModalOpen(true) }}
          >
            - Points
          </Button>
        </div>
      )
    }
  ]

  const totalClients = data?.data?.length ?? 0
  const bronze = data?.data?.filter((c: Client) => c.niveau_fidelite === 'BRONZE').length ?? 0
  const silver = data?.data?.filter((c: Client) => c.niveau_fidelite === 'SILVER').length ?? 0
  const gold = data?.data?.filter((c: Client) => c.niveau_fidelite === 'GOLD').length ?? 0
  const platinum = data?.data?.filter((c: Client) => c.niveau_fidelite === 'PLATINUM').length ?? 0

  return (
    <div>
      <h2>Programme de Fidélisation</h2>

      <Row gutter={16} style={{ marginBottom: 24 }}>
        <Col span={6}>
          <Card>
            <Statistic title="Total clients" value={totalClients} prefix={<GiftOutlined />} />
          </Card>
        </Col>
        <Col span={6}>
          <Card>
            <Statistic title="Bronze" value={bronze} valueStyle={{ color: 'orange' }} />
          </Card>
        </Col>
        <Col span={6}>
          <Card>
            <Statistic title="Silver" value={silver} valueStyle={{ color: 'gray' }} />
          </Card>
        </Col>
        <Col span={6}>
          <Card>
            <Statistic title="Gold / Platinum" value={gold + platinum} valueStyle={{ color: 'gold' }} />
          </Card>
        </Col>
      </Row>

      <Table
        columns={columns}
        dataSource={data?.data}
        rowKey="id"
        loading={isLoading}
      />

      <Modal
        title={`Créditer points — ${selectedClient?.nom} ${selectedClient?.prenom}`}
        open={isCrediterModalOpen}
        onCancel={() => setIsCrediterModalOpen(false)}
        onOk={() => crediterForm.submit()}
        confirmLoading={crediterMutation.isPending}
      >
        <Form form={crediterForm} layout="vertical" onFinish={(values) => crediterMutation.mutate(values)}>
          <Form.Item label="Points à créditer" name="points" rules={[{ required: true }]}>
            <Input type="number" min={1} />
          </Form.Item>
          <Form.Item label="Motif" name="motif">
            <Input />
          </Form.Item>
        </Form>
      </Modal>

      <Modal
        title={`Débiter points — ${selectedClient?.nom} ${selectedClient?.prenom}`}
        open={isDebiterModalOpen}
        onCancel={() => setIsDebiterModalOpen(false)}
        onOk={() => debiterForm.submit()}
        confirmLoading={debiterMutation.isPending}
      >
        <Form form={debiterForm} layout="vertical" onFinish={(values) => debiterMutation.mutate(values)}>
          <Form.Item label="Points à débiter" name="points" rules={[{ required: true }]}>
            <Input type="number" min={1} />
          </Form.Item>
          <Form.Item label="Motif" name="motif">
            <Input />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}