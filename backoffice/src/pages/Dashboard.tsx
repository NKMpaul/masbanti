import { Card, Row, Col, Statistic, Table, Tag } from 'antd'
import {
  ShopOutlined, UserOutlined, ShoppingCartOutlined,
  TeamOutlined, DollarOutlined
} from '@ant-design/icons'
import { useQuery } from '@tanstack/react-query'
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts'
import api from '../services/api'

const COLORS = ['#1890ff', '#52c41a', '#faad14', '#f5222d', '#722ed1', '#13c2c2']

export default function Dashboard() {
  const { data, isLoading } = useQuery({
    queryKey: ['dashboard'],
    queryFn: () => api.get('/dashboard/global').then(res => res.data)
  })

  if (isLoading) return <div>Chargement...</div>

  const commandesStatutData = data?.commandes_par_statut?.map((c: { statut: string; total: number }) => ({
    name: c.statut,
    value: c.total
  }))

  const niveauData = data?.clients_par_niveau?.map((c: { niveau_fidelite: string; total: number }) => ({
    name: c.niveau_fidelite,
    value: c.total
  }))

  const agenceColumns = [
    { title: 'Agence', dataIndex: 'nom', key: 'nom' },
    { title: 'Employés', dataIndex: 'employes_count', key: 'employes_count' },
    {
      title: 'Commandes', key: 'commandes',
      render: (_: unknown, record: { commandes: { total_commandes: number }[] }) =>
        record.commandes?.[0]?.total_commandes ?? 0
    },
    {
      title: 'Statut', dataIndex: 'statut', key: 'statut',
      render: (statut: string) => (
        <Tag color={statut === 'ACTIVE' ? 'green' : 'red'}>{statut}</Tag>
      )
    }
  ]

  return (
    <div>
      <h2>Dashboard — Vue Globale</h2>

      <Row gutter={16} style={{ marginBottom: 24 }}>
        <Col span={4}>
          <Card>
            <Statistic title="Agences" value={data?.total_agences} prefix={<ShopOutlined />} />
          </Card>
        </Col>
        <Col span={4}>
          <Statistic title="Clients" value={data?.total_clients} prefix={<UserOutlined />} />
        </Col>
        <Col span={4}>
          <Card>
            <Statistic title="Commandes" value={data?.total_commandes} prefix={<ShoppingCartOutlined />} />
          </Card>
        </Col>
        <Col span={4}>
          <Card>
            <Statistic title="Employés" value={data?.total_employes} prefix={<TeamOutlined />} />
          </Card>
        </Col>
        <Col span={8}>
          <Card>
            <Statistic
              title="Chiffre d'affaires"
              value={data?.chiffre_affaires}
              prefix={<DollarOutlined />}
              suffix="DH"
              precision={2}
            />
          </Card>
        </Col>
      </Row>

      <Row gutter={16} style={{ marginBottom: 24 }}>
        <Col span={12}>
          <Card title="Commandes par statut">
            <ResponsiveContainer width="100%" height={250}>
              <PieChart>
                <Pie
                  data={commandesStatutData}
                  dataKey="value"
                  nameKey="name"
                  cx="50%"
                  cy="50%"
                  outerRadius={80}
                  label={({ name, value }) => `${name}: ${value}`}
                >
                {commandesStatutData?.map((_entry: { name: string; value: number }, _index: number) => (
                <Cell key={_index} fill={COLORS[_index % COLORS.length]} />
                ))}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
          </Card>
        </Col>
        <Col span={12}>
          <Card title="Clients par niveau de fidélité">
            <ResponsiveContainer width="100%" height={250}>
              <PieChart>
                <Pie
                  data={niveauData}
                  dataKey="value"
                  nameKey="name"
                  cx="50%"
                  cy="50%"
                  outerRadius={80}
                  label={({ name, value }) => `${name}: ${value}`}
                >
                    {niveauData?.map((_entry: { name: string; value: number }, _index: number) => (
                    <Cell key={_index} fill={COLORS[_index % COLORS.length]} />
))}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
          </Card>
        </Col>
      </Row>

      <Row gutter={16} style={{ marginBottom: 24 }}>
        <Col span={24}>
          <Card title="Chiffre d'affaires par mois">
            <ResponsiveContainer width="100%" height={250}>
              <LineChart data={data?.ca_par_mois}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="mois" />
                <YAxis />
                <Tooltip />
                <Line type="monotone" dataKey="total" stroke="#1890ff" />
              </LineChart>
            </ResponsiveContainer>
          </Card>
        </Col>
      </Row>

      <Card title="Performance des agences">
        <Table
          columns={agenceColumns}
          dataSource={data?.agences_performance}
          rowKey="id"
          pagination={false}
        />
      </Card>
    </div>
  )
}