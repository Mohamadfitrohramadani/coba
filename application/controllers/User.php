<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Memeriksa apakah pengguna telah login
        if (!$this->session->userdata('email')) {
            redirect('login');
        }

        // Mendapatkan role_id dari sesi
        $role_id = $this->session->userdata('role_id');

        if ($role_id == 2) {
            // Jika role_id adalah 2, arahkan ke halaman tertentu atau berikan pesan kesalahan
            redirect('home');
        }
        // Jika role_id adalah 1 atau jenis lain yang diizinkan, biarkan pengguna melanjutkan

        $this->load->model('M_user');
    }
    public function index()
    {
        $user = $this->M_user->getDatauser();
        $DATA = array('data_user' => $user);
        $this->load->view('layout/header');
        $this->load->view('admin/navbar');
        $this->load->view('user/viewuser', $DATA);
        $this->load->view('layout/footer');
    }

    public function Inputuser()
    {
        $id = $this->input->post('id');
        $nama = $this->input->post('nama');
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        $role_id = $this->input->post('role_id');
    
        // Pengaturan konfigurasi upload gambar
        $config['upload_path'] = './assets/gambaruser/'; // Lokasi penyimpanan gambar
        $config['allowed_types'] = 'jpg|jpeg|png|gif'; // Jenis file yang diizinkan
        $config['max_size'] = 2048; // Ukuran maksimum (dalam KB)
    
        $this->load->library('upload', $config);
    
        if ($this->upload->do_upload('gambar_user')) {
            $gambar_data = $this->upload->data();
            $gambar_user = $gambar_data['file_name'];
    
            // Periksa jenis file gambar yang diunggah
            $file_info = getimagesize($gambar_data['full_path']);
            if ($file_info !== false) {
                // Lanjutkan menyimpan data ke database
                $DataInsert = array(
                    'id' => $id,
                    'nama' => $nama,
                    'gambar_user' => $gambar_user,
                    'email' => $email,
                    'password' => $password,
                    'role_id' => $role_id
                );
    
                if ($this->M_user->InsertDatauser($DataInsert)) {
                    // Input berhasil
                    $this->session->set_flashdata('success', 'Data user berhasil ditambahkan.');
                    redirect(base_url('user/'));
                } else {
                    // Input gagal
                    $this->session->set_flashdata('error', 'Gagal menambahkan data user.');
                    redirect(base_url('user/'));
                }
            } else {
                // File yang diunggah bukan gambar
                $this->session->set_flashdata('error', 'File yang diunggah bukan gambar.');
                redirect(base_url('user/'));
            }
        } else {
            // Jika pengunggahan gambar gagal
            $error = $this->upload->display_errors();
            $this->session->set_flashdata('error', 'Gagal mengunggah gambar: ' . $error);
            redirect(base_url('user/'));
        }
    }
    

    public function update($id)
    {
        $user = $this->M_user->getDatauserDetail($id);
        $DATA = array('data_user' => $user);
        $this->load->view('layout/header');
        $this->load->view('admin/navbar');
        $this->load->view('user/edituser', $DATA);
        $this->load->view('layout/footer');
    }
    public function updateuser()
{
    $id = $this->input->post('id');
    $nama = $this->input->post('nama');
    $email = $this->input->post('email');
    $password = $this->input->post('password');
    $role_id = $this->input->post('role_id');

    // Pengaturan konfigurasi upload gambar
    $config['upload_path'] = './assets/gambaruser/';
    $config['allowed_types'] = 'jpg|jpeg|png|gif';
    $config['max_size'] = 2048;

    $this->load->library('upload', $config);

    // Ambil data pengguna sebelumnya
    $user = $this->M_user->getDatauserDetail($id);

    // Cek apakah pengguna mengunggah gambar baru
    if ($this->upload->do_upload('gambar_user')) {
        // Hapus gambar lama jika ada
        if ($user->gambar_user && file_exists('./assets/gambaruser/' . $user->gambar_user)) {
            unlink('./assets/gambaruser/' . $user->gambar_user);
        }

        // Ambil informasi gambar yang diunggah
        $gambar_data = $this->upload->data();
        $gambar_user = $gambar_data['file_name'];
    } else {
        // Jika pengguna tidak mengunggah gambar baru, gunakan gambar lama
        $gambar_user = $user->gambar_user;
    }

    $DataUpdate = array(
        'id' => $id,
        'nama' => $nama,
        'gambar_user' => $gambar_user,
        'email' => $email,
        'password' => $password,
        'role_id' => $role_id
    );

    // Perbarui data pengguna
    $this->M_user->UpdateDatauser($DataUpdate, $id);

    redirect(base_url('user/'));
}


    public function delete($id)
    {
        $this->M_user->DeleteDatauser($id);
        redirect(base_url('user/'));
    }
}
