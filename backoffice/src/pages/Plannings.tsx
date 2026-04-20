import React, { useEffect, useState } from 'react';
import { Calendar, Badge, Modal, Button, Form, Select, DatePicker, TimePicker, Input, message } from 'antd';
import api from '../services/api';
import dayjs from 'dayjs';


// 🏷️ Typage exact de ton modèle Employé
interface Employe {
  id: string;
  nom: string;
  prenom: string;
}

interface Planning {
  id: string;
  employe_id: string; // Mis à jour
  date_planning: string;
  heure_debut: string;
  heure_fin: string;
  statut: string;
  employe: { nom: string; prenom: string };
}

interface FormValues {
  employe_id: string;
  date: dayjs.Dayjs;
  heure_debut: dayjs.Dayjs;
  heure_fin: dayjs.Dayjs;
  statut: string;
  notes?: string;
}

const Plannings: React.FC = () => {
  const [plannings, setPlannings] = useState<Planning[]>([]);
  const [employes, setEmployes] = useState<Employe[]>([]); // State pour tes vrais employés
  const [isModalVisible, setIsModalVisible] = useState(false);
  const [form] = Form.useForm();

  const fetchData = async () => {
    try {
      const [resPlannings, resEmployes] = await Promise.all([
        api.get('/plannings'),
        api.get('/employes'), // On appelle ta vraie table !
      ]);
      setPlannings(resPlannings.data);
      setEmployes(resEmployes.data);
    } catch {
      message.error("Erreur lors de la récupération des données.");
    }
  };

  useEffect(() => {
    const loadData = async () => {
      await fetchData();
    };
    loadData();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleFinish = async (values: FormValues) => {
    try {
      await api.post('/plannings', {
        employe_id: values.employe_id,
        date_planning: values.date.format('YYYY-MM-DD'),
        heure_debut: values.heure_debut.format('HH:mm'),
        heure_fin: values.heure_fin.format('HH:mm'),
        statut: values.statut,
        notes: values.notes || null,
      });
      message.success("Planning enregistré avec succès !");
      setIsModalVisible(false);
      form.resetFields();
      await fetchData();
    } catch {
      message.error("Impossible d'enregistrer le planning.");
    }
  };

  return (
    <div style={{ padding: 24, background: '#fff' }}>
      <h2>🗓️ Gestion des Ressources Humaines (Plannings)</h2>

      <Button type="primary" onClick={() => setIsModalVisible(true)} style={{ marginBottom: 16 }}>
        + Assigner un horaire
      </Button>

      <Calendar cellRender={(value) => {
        const listData = plannings.filter((p) => p.date_planning === value.format('YYYY-MM-DD'));
        return (
          <ul style={{ listStyle: 'none', padding: 0 }}>
            {listData.map((item) => (
              <li key={item.id}>
                <Badge status={item.statut === 'DISPONIBLE' ? 'success' : 'error'} text={`${item.employe?.prenom} ${item.employe?.nom}`} />
              </li>
            ))}
          </ul>
        );
      }} />

      <Modal
        title="Assigner un horaire à un employé"
        open={isModalVisible}
        onCancel={() => setIsModalVisible(false)}
        onOk={() => form.submit()}
        destroyOnHidden // Pour corriger l'avertissement jaune !
      >
        <Form form={form} layout="vertical" onFinish={handleFinish}>
          
                <Form.Item name="employe_id" label="Sélectionner l'Employé" rules={[{ required: true }]}>
        <Select 
            placeholder="Choisir un employé"
            options={
            Array.isArray(employes) 
                ? employes.map((emp) => ({
                    value: emp.id,
                    label: `${emp.prenom} ${emp.nom}`,
                }))
                : [] // 👈 Si ce n'est pas un tableau, on renvoie une liste vide au lieu de faire crasher le site !
            }
        />
        </Form.Item>

          <Form.Item name="date" label="Date" rules={[{ required: true }]}>
            <DatePicker style={{ width: '100%' }} />
          </Form.Item>

          <div style={{ display: 'flex', gap: '10px' }}>
            <Form.Item name="heure_debut" label="Heure Début" rules={[{ required: true }]} style={{ flex: 1 }}>
              <TimePicker format="HH:mm" style={{ width: '100%' }} />
            </Form.Item>

            <Form.Item name="heure_fin" label="Heure Fin" rules={[{ required: true }]} style={{ flex: 1 }}>
              <TimePicker format="HH:mm" style={{ width: '100%' }} />
            </Form.Item>
          </div>

          <Form.Item name="statut" label="Statut" rules={[{ required: true }]}>
            <Select 
                placeholder="Choisir un statut"
                options={[
                { value: 'DISPONIBLE', label: 'Disponible' },
                { value: 'EN_MISSION', label: 'En mission' },
                { value: 'CONGE', label: 'En congé' },
                { value: 'ABSENT', label: 'Absent' },
                ]}
            />
            </Form.Item>

          <Form.Item name="notes" label="Notes internes (Optionnel)">
            <Input style={{ width: '100%' }} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  );
};

export default Plannings;