import { useState } from 'react'
import { Card, Row, Col, DatePicker, Button, Table, Select, Statistic, message } from 'antd'
import { DownloadOutlined } from '@ant-design/icons'
import { useQuery } from '@tanstack/react-query'
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar } from 'recharts'
import api from '../services/api'
import dayjs from 'dayjs'

interface CommandeRow {
  id: string
  numero_commande: string
  statut: string
  montant_total: number
  created_at: string
  client: { nom: string; prenom: string }
  agence: { nom: string }
}

interface FactureRow {
  id: string
  numero_facture: string
  statut: string
  montant_ttc: number
  date_emission: string
  client: { nom: string; prenom: string }
}

const { RangePicker } = DatePicker

export default function Rapports() {
  const [dateRange, setDateRange] = useState<[string, string]>([
    dayjs().startOf('month').format('YYYY-MM-DD'),
    dayjs().format('YYYY-MM-DD')
  ])
  const [selectedAgence, setSelectedAgence] = useState<string>('')

  const { data: dashboard } = useQuery({
    queryKey: ['dashboard'],
    queryFn: () => api.get('/dashboard/global').then(res => res.data)
  })

  const { data: agences } = useQuery({
    queryKey: ['agences'],
    queryFn: () => api.get('/agences').then(res => res.data)
  })

 const { data: commandes } = useQuery({
  queryKey: ['commandes', selectedAgence],
  queryFn: () => api.get('/commandes', {
    params: selectedAgence ? { agence_id: selectedAgence } : {}
  }).then(res => res.data)
})

const { data: factures } = useQuery({
  queryKey: ['factures', selectedAgence],
  queryFn: () => api.get('/factures', {
    params: selectedAgence ? { agence_id: selectedAgence } : {}
  }).then(res => res.data)
})

  const exportCSV = (data: unknown[], filename: string) => {
    if (!data || data.length === 0) {
      message.warning('Aucune donnée à exporter.')
      return
    }
    const headers = Object.keys(data[0] as object).join(',')
    const rows = data.map((row: unknown) =>
      Object.values(row as object).map(v =>
        typeof v === 'object' ? JSON.stringify(v) : v
      ).join(',')
    )
    const csv = [headers, ...rows].join('\n')
    const blob = new Blob([csv], { type: 'text/csv' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `${filename}_${dayjs().format('YYYY-MM-DD')}.csv`
    a.click()
    URL.revokeObjectURL(url)
    message.success('Export CSV téléchargé !')
  }

  const commandesColumns = [
  { title: 'Numéro', dataIndex: 'numero_commande', key: 'numero_commande' },
  {
    title: 'Client', key: 'client',
    render: (_: unknown, r: CommandeRow) => `${r.client?.nom} ${r.client?.prenom}`
  },
  {
    title: 'Agence', key: 'agence',
    render: (_: unknown, r: CommandeRow) => r.agence?.nom
  },
  { title: 'Statut', dataIndex: 'statut', key: 'statut' },
  {
    title: 'Montant', dataIndex: 'montant_total', key: 'montant_total',
    render: (m: number) => `${m} DH`
  },
  {
    title: 'Date', dataIndex: 'created_at', key: 'created_at',
    render: (d: string) => d?.slice(0, 10)
  },
]

const facturesColumns = [
  { title: 'Numéro', dataIndex: 'numero_facture', key: 'numero_facture' },
  {
    title: 'Client', key: 'client',
    render: (_: unknown, r: FactureRow) => `${r.client?.nom} ${r.client?.prenom}`
  },
  {
    title: 'Montant TTC', dataIndex: 'montant_ttc', key: 'montant_ttc',
    render: (m: number) => `${m} DH`
  },
  { title: 'Statut', dataIndex: 'statut', key: 'statut' },
  {
    title: 'Date', dataIndex: 'date_emission', key: 'date_emission',
    render: (d: string) => d?.slice(0, 10)
  },
]

  return (
    <div>
      <h2>Rapports & Exports</h2>

      {/* Filtres */}
      <Card style={{ marginBottom: 24 }}>
        <Row gutter={16} align="middle">
          <Col>
            <RangePicker
              value={[dayjs(dateRange[0]), dayjs(dateRange[1])]}
              onChange={(dates) => {
                if (dates) {
                  setDateRange([
                    dates[0]!.format('YYYY-MM-DD'),
                    dates[1]!.format('YYYY-MM-DD')
                  ])
                }
              }}
            />
          </Col>
          <Col>
            <Select
              placeholder="Toutes les agences"
              style={{ width: 200 }}
              allowClear
              onChange={v => setSelectedAgence(v ?? '')}
              options={agences?.data?.map((a: { id: string; nom: string }) => ({
                value: a.id,
                label: a.nom
              }))}
            />
          </Col>
        </Row>
      </Card>

      {/* Statistiques globales */}
      <Row gutter={16} style={{ marginBottom: 24 }}>
        <Col span={6}>
          <Card>
            <Statistic
              title="Total commandes"
              value={dashboard?.total_commandes ?? 0}
            />
          </Card>
        </Col>
        <Col span={6}>
          <Card>
            <Statistic
              title="Chiffre d'affaires"
              value={dashboard?.chiffre_affaires ?? 0}
              suffix="DH"
              precision={2}
            />
          </Card>
        </Col>
        <Col span={6}>
          <Card>
            <Statistic
              title="Total clients"
              value={dashboard?.total_clients ?? 0}
            />
          </Card>
        </Col>
        <Col span={6}>
          <Card>
            <Statistic
              title="Total agences"
              value={dashboard?.total_agences ?? 0}
            />
          </Card>
        </Col>
      </Row>

      {/* Graphiques */}
      <Row gutter={16} style={{ marginBottom: 24 }}>
        <Col span={12}>
          <Card title="Commandes par statut">
            <ResponsiveContainer width="100%" height={250}>
              <BarChart data={dashboard?.commandes_par_statut?.map((c: { statut: string; total: number }) => ({
                name: c.statut,
                total: c.total
              }))}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="name" tick={{ fontSize: 10 }} />
                <YAxis />
                <Tooltip />
                <Bar dataKey="total" fill="#1890ff" />
              </BarChart>
            </ResponsiveContainer>
          </Card>
        </Col>
        <Col span={12}>
          <Card title="CA par mois">
            <ResponsiveContainer width="100%" height={250}>
              <LineChart data={dashboard?.ca_par_mois?.map((c: { mois: number; total: number }) => ({
                name: `Mois ${c.mois}`,
                total: c.total
              }))}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="name" />
                <YAxis />
                <Tooltip />
                <Line type="monotone" dataKey="total" stroke="#52c41a" />
              </LineChart>
            </ResponsiveContainer>
          </Card>
        </Col>
      </Row>

      {/* Rapport commandes */}
      <Card
        title="Rapport des Commandes"
        style={{ marginBottom: 24 }}
        extra={
          <Button
            icon={<DownloadOutlined />}
            onClick={() => exportCSV(commandes?.data ?? [], 'commandes')}
          >
            Export CSV
          </Button>
        }
      >
        <Table
          columns={commandesColumns}
          dataSource={commandes?.data}
          rowKey="id"
          pagination={{ pageSize: 5 }}
        />
      </Card>

      {/* Rapport factures */}
      <Card
        title="Rapport des Factures"
        extra={
          <Button
            icon={<DownloadOutlined />}
            onClick={() => exportCSV(factures?.data ?? [], 'factures')}
          >
            Export CSV
          </Button>
        }
      >
        <Table
          columns={facturesColumns}
          dataSource={factures?.data}
          rowKey="id"
          pagination={{ pageSize: 5 }}
        />
      </Card>
    </div>
  )
}